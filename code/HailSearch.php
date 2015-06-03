<?php

/**
 * HailSearch
 * Page type to Search SiteTree and {@link HailArticle} objects.
 *
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 */

class HailSearch extends Page {
	
	
	private static $defaults = array('PageLength' => '10', 'Combined' => true);
	
	private static $db = array(
		'Combined' => 'boolean',
		'PageLength' => 'int'
	);
	
	
	private static $has_one = array(
		'HailHolder' => 'HailHolder'
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->removeFieldFromTab("Root.Main","Content");
		
		$combined = new FieldGroup(new CheckboxField("Combined", 'Include site results with Hail results'));
		$combined->setTitle('Combined search');
		$fields->addFieldToTab('Root.Main', $combined);
		
		$holder_field = new DropdownField('HailHolderID', 'Where should results link to?', HailHolder::get()->map('ID', 'Title'));
		$fields->addFieldToTab('Root.Main', $holder_field);
		
		$fields->addFieldToTab('Root.Main', new NumericField(
			'PageLength',
			'Number of results per page',
			$this->PageLength
		));

		return $fields;
	}
}

/**
 * Controller for {@link HailSearch} page type
 * Provides the core logic and controller action to Search {@link HailArticle}
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 */

class HailSearch_Controller extends Page_Controller {
	
	/**
	 * Returns a list of results for the current request parameters. 
	 * This function was adapted from SeacrhFor::getResults()
	 * 
	 * @return DataList
	 */
	public function HailResults(){
	 	$data = $_REQUEST;
		
		// set language (if present)
		if(class_exists('Translatable')) {
			if(singleton('SiteTree')->hasExtension('Translatable') && isset($data['searchlocale'])) {
				if($data['searchlocale'] == "ALL") {
					Translatable::disable_locale_filter();
				} else {
					$origLocale = Translatable::get_current_locale();

					Translatable::set_current_locale($data['searchlocale']);
				}
			}
		}

		// Get keyword to search
		if (empty($data['q']) || !trim($data['q'])) {
			return;
		}
		
		$keywords = $data['q'];

	 	$andProcessor = create_function('$matches','
	 		return " +" . $matches[2] . " +" . $matches[4] . " ";
	 	');
	 	$notProcessor = create_function('$matches', '
	 		return " -" . $matches[3];
	 	');

	 	$keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
	 	$keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);
		
		$keywords = $this->addStarsToKeywords($keywords);

		// Set pagination
		$pageLength = $this->PageLength;
		if(!$pageLength || $pageLength < 1) {
			
			$pageLength = 10;
		}
		
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		
		// Pick wich class to search
		if ($this->Combined) {
			$classesToSearch = array('SiteTree', 'HailArticle');
		} else {
			$classesToSearch = array('HailArticle');
		}
		
		if(strpos($keywords, '"') !== false || strpos($keywords, '+') !== false || strpos($keywords, '-') !== false || strpos($keywords, '*') !== false) {
			$results = $this->searchEngine($classesToSearch,$keywords, $start, $pageLength, "\"Relevance\" DESC", "", true);
		} else {
			$results = $this->searchEngine($classesToSearch, $keywords, $start, $pageLength);
		}
		
		// filter by permission
		if($results) foreach($results as $result) {
			if(!$result->canView()) $results->remove($result);
		}
		
		// reset locale
		if(class_exists('Translatable')) {
			if(singleton('SiteTree')->hasExtension('Translatable') && isset($data['searchlocale'])) {
				if($data['searchlocale'] == "ALL") {
					Translatable::enable_locale_filter();
				} else {
					Translatable::set_current_locale($origLocale);
				}
			}
		}
		
		// Create links to our HailArticle
		if ($this->HailHolder()) {
			foreach ($results as $r) {
				if ($r->ClassName == 'HailArticle') {
					$r->Link = $this->HailHolder()->Link('article/' . $r->ID);
				} elseif ($r->Content) {
					$r->Lead = $r->Content;
				}
			}
		}

		return $results;
	}
	
	// This is another class copied from SearchForm
	private function addStarsToKeywords($keywords) {
		if(!trim($keywords)) return "";
		// Add * to each keyword
		$splitWords = preg_split("/ +/" , trim($keywords));
		while(list($i,$word) = each($splitWords)) {
			if($word[0] == '"') {
				while(list($i,$subword) = each($splitWords)) {
					$word .= ' ' . $subword;
					if(substr($subword,-1) == '"') break;
				}
			} else {
				$word .= '*';
			}
			$newWords[] = $word;
		}
		return implode(" ", $newWords);
	}
	
	/**
	 * Adapted from the standard SilverStripe Search engine
	 *
	 * @param string $keywords Keywords as a string.
	 */
	private function searchEngine($classesToSearch, $keywords, $start, $pageLength, $sortBy = "Relevance DESC",
		$extraFilter = "", $booleanSearch = false, $alternativeFileFilter = "", $invertedMatch = false) {
		
		$fileFilter = '';
		$keywords = Convert::raw2sql($keywords);
		$htmlEntityKeywords = htmlentities($keywords, ENT_NOQUOTES, 'UTF-8');

		$extraFilters = array('SiteTree' => '', 'HailArticle' => '');

		if($booleanSearch) $boolean = "IN BOOLEAN MODE";

		if($extraFilter) {
			$extraFilters['SiteTree'] = " AND $extraFilter";

			if($alternativeFileFilter) $extraFilters['File'] = " AND $alternativeFileFilter";
			else $extraFilters['HailArticle'] = $extraFilters['SiteTree'];
		}

		// Always ensure that only pages with ShowInSearch = 1 can be searched
		$extraFilters['SiteTree'] .= " AND ShowInSearch <> 0";
		
		$limit = $start . ", " . (int) $pageLength;

		$notMatch = $invertedMatch ? "NOT " : "";
		if($keywords) {
			$match['SiteTree'] = "
				MATCH (Title, MenuTitle, Content, MetaDescription) AGAINST ('$keywords' $boolean)
				+ MATCH (Title, MenuTitle, Content, MetaDescription) AGAINST ('$htmlEntityKeywords' $boolean)
			";
			$match['HailArticle'] = "
				MATCH (Title,Lead,Author,Content) AGAINST ('$keywords' $boolean)
				+ MATCH (Title,Lead,Author,Content) AGAINST ('$htmlEntityKeywords' $boolean)
			";

			// We make the relevance search by converting a boolean mode search into a normal one
			$relevanceKeywords = str_replace(array('*','+','-'),'',$keywords);
			$htmlEntityRelevanceKeywords = str_replace(array('*','+','-'),'',$htmlEntityKeywords);
			$relevance['SiteTree'] = "MATCH (Title, MenuTitle, Content, MetaDescription) "
				. "AGAINST ('$relevanceKeywords') "
				. "+ MATCH (Title, MenuTitle, Content, MetaDescription) AGAINST ('$htmlEntityRelevanceKeywords')";
			$relevance['HailArticle'] = "
				MATCH (Title,Lead,Author,Content) AGAINST ('$relevanceKeywords') +
				MATCH (Title,Lead,Author,Content) AGAINST ('$htmlEntityRelevanceKeywords')
			";
		} else {
			$relevance['SiteTree'] = $relevance['HailArticle'] = 1;
			$match['SiteTree'] = $match['HailArticle'] = "1 = 1";
		}

		// Generate initial DataLists and base table names
		$lists = array();
		$baseClasses = array('SiteTree' => '', 'HailArticle' => '');
		foreach($classesToSearch as $class) {
			$lists[$class] = DataList::create($class)->where($notMatch . $match[$class] . $extraFilters[$class], "");
			$baseClasses[$class] = '"'.$class.'"';
		}

		// Make column selection lists
		$select = array(
			'SiteTree' => array(
				"ClassName", "$baseClasses[SiteTree].\"ID\"", "ParentID",
				"Title", "MenuTitle", "URLSegment", "Content",
				"LastEdited", "Created",
				"Relevance" => $relevance['SiteTree'], "CanViewType" ),
			'HailArticle' => array(
				"ClassName"=>"_utf8'HailArticle'", "$baseClasses[HailArticle].\"ID\"",
				"Title", "Content",
				"Relevance" => $relevance['HailArticle'], "CanViewType" => "NULL",
				
			),
		);

		// Process and combine queries
		$querySQLs = array();
		$totalCount = 0;
		foreach($lists as $class => $list) {
			$query = $list->dataQuery()->query();

			// There's no need to do all that joining
			$query->setFrom(array(str_replace(array('"','`'), '', $baseClasses[$class]) => $baseClasses[$class]));
			//$this->setSelect($query, $select[$class]);
			
			foreach ($select[$class] as $idx => $field) {
				$query->selectField($field, is_numeric($idx) ? null : $idx);
			}
			
			//$query->setSelect($select[$class]);
			
			
			$query->setOrderBy(array());
			
			# We get an extra where clause from our data list query that tries to match HailAPIObject
			# We need to remove this where clause
			if ($class=='HailArticle') {
				$wheres = $query->getWhere();
				array_pop($wheres);
				$query->setWhere($wheres);
				
				$query->addInnerJoin('HailApiObject', 'HailArticle.ID = HailApiObject.ID');
			};
			
			$querySQLs[] = $query->sql();
			
			$totalCount += $query->unlimitedRowCount();
		}


		$objects = array();
		
		foreach($querySQLs as $query) {
			$records = DB::query($query);
			foreach($records as $record) {
				$objects[] = new $record['ClassName']($record);
			}
		}

		$list = new ArrayList($objects);
		$list = $list->sort('Relevance', 'DESC');

		$plist = new PaginatedList($list, $this->request);
		$plist->setPageStart($start);
		$plist->setPageLength($pageLength);
		$plist->setTotalItems($totalCount);

		// The list has already been limited by the query above
		//$plist->setLimitItems(false);

		return $plist;
	}
	
	/**
	 * Returns the keywords form the request.
	 * 
	 * @return string
	 */
	public function SearchTerm() {
		if (empty($_REQUEST['q'])) {
			return '';
		} else {
			return htmlentities(trim($_REQUEST['q']));
		}
	}
	
	public function setSelect(&$query, $fields) {
		//var_dump($fields);die();
		//$query->select = array();
		/*if (func_num_args() > 1) {
			$fields = func_get_args();
		} else if(!is_array($fields)) {
			$fields = array($fields);
		}*/
		return $this->addSelect($query, $fields);
	}
	
	public function addSelect(&$query, $fields) {
		/*if (func_num_args() > 1) {
			$fields = func_get_args();
		} else if(!is_array($fields)) {
			$fields = array($fields);
		}*/

		foreach($fields as $idx => $field) {
			if (is_array($field)) {
				var_dump($field);
			}
			if(preg_match('/^(.*) +AS +"?([^"]*)"?/i', $field, $matches)) {
				$query->selectField($matches[1], $matches[2]);
			} else {
				$query->selectField($field, is_numeric($idx) ? null : $idx);
			}
		}
		
		return $query;
	}

}
