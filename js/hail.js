(function ( $ ) {

	$.fn.hailList = function( options ) {

		// This is the easiest way to have default options.
		var settings = $.extend({
			nextSelector: ".next",
			backSelector: ".back",
			nextFunction: function() {},
			backFunction: function() {},
			dateFormat: 'ddd, MMMM d, yyyy',
			reponsiveDisplay: {0: 1, 768: 2, 992: 3}
		}, options );

		// Greenify the collection based on the settings variable.
		return this.each(function () {
			var $this = $(this);
			var listID = $this.data('list');
			var objectType = $this.data('object-type');
			if (typeof objectType == 'undefined' || objectType == '') objectType = 'HailList';
			var listCount = parseInt($this.data('count'));
			var pos = 0;
			var display = $this.find('.HailArticle').length;

			$this.find(settings.nextSelector).click(next);
			$this.find(settings.backSelector).click(back);

			resize();
			$( window ).resize(resize);

			function resize() {
				currentDisplay = display;
				for (minRes in settings.reponsiveDisplay) {

					if ($('body').outerWidth(true) >= minRes) {
						currentDisplay = settings.reponsiveDisplay[minRes];
					} else {
						break;
					}
				}

				if (listCount <= currentDisplay) {
					$this.find(settings.nextSelector).hide();
					$this.find(settings.backSelector).hide();
				} else {
					$this.find(settings.nextSelector).show();
					$this.find(settings.backSelector).show();
				}
			}

			function next(event) {
				event.preventDefault();
				pos = (pos+1) % listCount;
				get(pos+display-1, settings.nextFunction);
			}

			function back(event) {
				event.preventDefault();
				pos = (pos + listCount - 1);
				pos = (pos ) % listCount;


				listCount

				get(pos+0, settings.backFunction);
			}

			function get(fetchNumber, func) {
				returnObj = {};

				function updateImage(article, clone) {
					if (article.HeroImage != null && article.HeroImage.id != 0) {
						$.getJSON(
							article.HeroImage.href, 'add_fields=RelativeCenterX,RelativeCenterY',
							function (data) {
								$.each(
									['Url150Square', 'Url500', 'Url500Square', 'Url1000', 'Url1000Square', 'Url2000', 'Urloriginal'],
									function (i, imgClass) {
										clone.find('img.' + imgClass).attr('src', data[imgClass]).show();
										clone.find('.background' + imgClass).css('background-image', 'url(\'' + data[imgClass] + '\')')
											.css('background-position', data.RelativeCenterX + '% ' + data.RelativeCenterY + '%' ).show();
									}
								);
								func(returnObj);
							}
						);
					} else if (article.HeroVideo != null && article.HeroVideo.id != 0) {
				$.getJSON(
					article.HeroVideo.href, '',
					function (data) {
						$.each(
							['Url150Square', 'Url500', 'Url500Square', 'Url1000', 'Url1000Square', 'Url2000', 'Urloriginal'],
							function (i, imgClass) {
								clone.find('img.' + imgClass).attr('src', data[imgClass]).show();
								clone.find('.background' + imgClass).css('background-image', 'url(\'' + data[imgClass] + '\')')
									.css('background-position', data.RelativeCenterX + '% ' + data.RelativeCenterY + '%' ).show();
							}
						);
						func(returnObj);
					}
				);
					} else {
						$.each(
							['Url150Square', 'Url500', 'Url500Square', 'Url1000', 'Url1000Square', 'Url2000', 'Urloriginal'],
							function (i, imgClass) {
								clone.find('img.' + imgClass).hide();
								clone.find('.background' + imgClass).hide();
							}
						);
						func(returnObj);
					}
				}

				fetchNumber = fetchNumber % listCount;

				var endpoint = '';
				if (typeof listID == 'undefined' || listID == '') {
					endpoint = 'api/v1/' + objectType + '.json'
				} else {
					endpoint = 'api/v1/'  + objectType + '/' + listID + '/Articles.json';
				}

				$.getJSON(
					endpoint,
					'limit=1&start=' + fetchNumber,
					function (data) {
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

						updateImage(article, clone);

					}
				);
			}

			// Updates most text based property for our new article
			function updateArticle(article, clone) {
				$.each(
					['Title', 'Author', 'Content', 'Location', 'Rating', 'Urloriginal'],
					function (i, textClass) {
						clone.find('.' + textClass).html(article[textClass]);
					}
				);

				$.each(
					['Lead', 'Editorial'],
					function (i, textClass) {
						clone.find('.' + textClass).html(trimByWord(article[textClass], 50));
					}
				);

				clone.find('a.Link').each(function (i,e) {
					var href = $(e).attr('href');
					href = href.replace(/\/\d*$/g, '/' + article['ID']);
					$(e).attr('href', href);
				});

				clone.find('a.Url').each(function (i,e) {
					var href = $(e).attr('href');
					href = article['Url'];
					$(e).attr('href', href);
				});
			}

			function updateDate(article, clone) {
				$.each(
					['Date'],
					function (i, dateClass) {
						clone.find('.' + dateClass).html(formatDate(article[dateClass]));
					}
				);
			}

			function trimByWord(sentence, numberOfWords) {
				if (sentence === undefined || sentence === null) {
					return '';
				}
				var result = sentence;
				var resultArray = result.split(" ");
				if(resultArray.length > numberOfWords){
					resultArray = resultArray.slice(0, numberOfWords);
					result = resultArray.join(" ") + " ...";
				}
				return result;
			}
			function formatDate(dateData) {
				if (dateData === undefined) {
					return '';
				} else {
					return $.format.date(dateData, settings.dateFormat);
				}
			}

		});

	};

}( jQuery ));
