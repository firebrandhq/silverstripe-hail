(function ( $ ) {
 
	$.fn.hailList = function( options ) {

		// This is the easiest way to have default options.
		var settings = $.extend({
			nextSelector: ".next",
			backSelector: ".back",
			nextFunction: function() {},
			backFunction: function() {},
			dateFormat: 'ddd, MMMM d, yyyy'
		}, options );

		// Greenify the collection based on the settings variable.
		return this.each(function () {
			var $this = $(this);
			var listID = $this.attr('data-list');
			var listCount = parseInt($this.attr('data-count'));
			var pos = 0;
			var display = $this.find('.HailArticle').length;
			
			$this.find(settings.nextSelector).click(next);
			$this.find(settings.backSelector).click(back);
			
			function next(event) {
				event.preventDefault();
				pos = (pos+1) % listCount;
				get(pos+display, settings.nextFunction);
			}
			
			function back(event) {
				event.preventDefault();
				pos = (pos + listCount - 1);
				console.log(pos);
				pos = (pos ) % listCount;
				console.log(pos);
				
				listCount
				
				get(pos+0, settings.backFunction);
			}
			
			function get(fetchNumber, func) {
				returnObj = {};
				
				function updateImage(HeroImage, clone) {
					if (HeroImage != null) {
						$.getJSON(
							article.HeroImage.href, '',
							function (data) {
								$.each(
									['Url150Square', 'Url500', 'Url500Square', 'Url1000', 'Url1000Square', 'Url2000', 'Urloriginal'],
									function (i, imgClass) {
										clone.find('img.' + imgClass).attr('src', data[imgClass]).show();
									}
								);
								func(returnObj);
							}
						);
					}
					else {
						clone.find('img.' + imgClass).hide();
					}
				}
			
				fetchNumber = fetchNumber % listCount;
			
				$.getJSON(
					'api/v1/HailList/' + listID + '/Articles.json',
					'limit=1&start=' + fetchNumber,
					function (data) {
						console.dir(data);
						article = data.items[0];
						
						var clone = $this.find('.HailArticle').first().clone();
						updateArticle(article, clone);
						updateDate(article, clone);
						
						returnObj = {
							hailList: $this,
							article: article,
							listID: listID,
							clone: clone,
							pos: pos
						};
						
						updateImage(article.HeroImage, clone);
						
						
					}
				);
			}

			
			function updateArticle(article, clone) {
				$.each(
					['Title', 'Author', 'Lead', 'Content', 'Location', 'Rating', 'Urloriginal'],
					function (i, textClass) {
						clone.find('.' + textClass).html(article[textClass]);
					}
				);
			}
			
			function updateDate(article, clone) {
				$.each(
					['Date'],
					function (i, dateClass) {
						clone.find('.' + dateClass).html($.format.date(article[dateClass], settings.dateFormat));
					}
				);
			}

		});

	};
 
}( jQuery ));
