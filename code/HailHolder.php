<?php

/**
 * HailHolder
 * Page type to displays lists of {@link HailArticle} via {@link HailList}.
 *
 * It provides options to display :
 * - individual {@link HailArticle},
 * - list of {@link HailArticle} via {@link HailList},
 * - and lists of {@link HailList}
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 * @method HasManyList List() List of {@link HailList}
 */

class HailHolder extends Page {

	private static  $has_many = array(
		'Lists' => 'HailList'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$config = GridFieldConfig_RecordEditor::create();

		$addButton = new GridFieldAddNewMultiClass();
		$addButton->setClasses(self::getHailListClasses());
		$config->addComponent($addButton)->removeComponentsByType('GridFieldAddNewButton');


		$config->addComponent(new GridFieldSortableRows('SortOrder'));
		$list = new GridField("Lists", "Lists", $this->Lists()->sort("SortOrder"), $config);
		$fields->addFieldToTab('Root.HailList', $list);
		$fields->addFieldToTab('Root.HailList', $list);

		return $fields;
	}


	/**
	 * Returns the first articles with an image.
	 *
	 * @return HailArticle First Article with image
	 */
	public function getFirst() {
		foreach ($this->Lists()->sort('SortOrder') as $list) {
			foreach ($list->Articles() as $art) {
				if ($art->HeroImage()) {
					return $art;
				}
			}
		}
	}

	private static function getHailListClasses() {
		$basicList = array('HailList','TagHailList','PublicationHailList');
		$configList = static::config()->HailListClasses;
		if ($configList) {
			if (is_array($configList)) {
				$basicList = array_merge($basicList, $configList);
			} else {
				$basicList = array_merge($basicList, explode(',', $configList));
			}
		}
		return $basicList;
	}
}

/**
 * Controller for type {@link HailHolder} page type
 * Provides the core logic and controller action to display {@link HailArticle}
 *
 * @package hail
 * @author Maxime Rainville, Firebrand
 * @version 1.0
 *
 */

class HailHolder_Controller extends Page_Controller {


	private static $allowed_actions = array (
		'haillist', 'hailarticle'
	);

	private static $url_handlers = array(
		'list/$ID' => 'haillist',
		'article/$ID' => 'hailarticle'
	);

	/**
	 * Action to list the {@link HailArticle} in a specific {@link HailList}
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText First Article with image
	 */
	public function haillist($request) {
		$this->myList = $this->Lists()->byID($request->param('ID'));

		return $this->renderWith(array('HailHolder_HailList', 'Page'));
	}

	/**
	 * Action to display a sepcific {@link HailArticle}
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText First Article with image
	 */
	public function hailarticle($request) {
		$this->myArticle = HailArticle::get()->byID($request->param('ID'));

		$this->myArticle->refresh();

		if ($this->myArticle->Content) {
			$this->myArticle->softRefresh();
		} else {
			$this->myArticle->refresh();
		}

		return $this->renderWith(array('HailHolder_HailArticle', 'Page'));
	}

	/**
	 * Generate a URL to render a specific {@link HailList}
	 *
	 * @param int $listID
	 * @return String url
	 */
	public function ListLink($listID) {
		return Controller::join_links('list/' . $listID, 'list');
	}



	/**
	 * Return the {@link HailList} to render when the {@link haillist()} action is invoke.
	 *
	 * @return {@link HailList} List to display
	 */
	public function MyList() {
		return $this->myList;
	}
	protected $myList;

	public function MyPaginatedListArticles() {
		$plist = new PaginatedList($this->myList->Articles(), $this->request);
		$plist->setPageLength(20);
		return $plist;
	}


	/**
	 * Return the {@link HailArticle} to display when the {@link hailarticle()} action is invoke
	 *
	 * @return {@link HailList} List to display
	 */
	public function MyArticle() {
		return $this->myArticle;
	}
	protected $myArticle;


	/**
	 * Returns a relevant hero {@link HailImage} for the current action.
	 *
	 * @return HailImage Hero image of a relevant article
	 */
	public function HeroImage() {
		$hero = false;
		switch($this->getAction()) {
			case 'hailarticle':
				$hero = $this->myArticle->HeroImage();
				break;
			case 'haillist':
				$article = $this->myList->Articles()->First();
				if ($article) {
					$hero = $article->HeroImage();
				}
				break;
			case 'index':
				$article = $this->getFirst();
				if ($article) {
					$hero = $article->HeroImage();
				}
				break;
		}

		return $hero;

	}

	/**
	 * Returns a sorted list of {@link HailList}.
	 *
	 * @return ArrayList {@link HailList} sorted
	 */
	public function Lists() {
		return $this->dataRecord->Lists()->sort('SortOrder');
	}

	/**
	 * Returns a relevant title for the current action.
	 *
	 * @return string
	 */
	public function getTitle() {
		switch($this->getAction()) {
			case 'hailarticle':
				return $this->myArticle->Title;
				break;
			case 'haillist':
				return $this->myList->getTitle();
				break;
			default:
				return parent::getTitle();
				break;
		}
	}

	/**
	 * Returns a relevant metadata description for the current action.
	 *
	 * @return string
	 */
	public function getMetaDescription() {
		switch($this->getAction()) {
			case 'hailarticle':
				return $this->myArticle->Lead;
				break;
			case 'haillist':
				return $this->myList->Description;
				break;
			default:
				return $this->dataRecord->MetaDescription;
				break;
		}
	}

}
