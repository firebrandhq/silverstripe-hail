//Hail Lists
$('.HailList').hailList({nextFunction: nextHailArticle, backFunction: backHailArticle});

function nextHailArticle(event) {
	// var nextLead = event.article.Lead.trim().substr(0,200) + '...';
	var slideInner = event.hailList.find('.slide-inner');
	slideInner.
		append(event.clone).
		addClass('animate');
	slideInner.addClass('next');

	setTimeout(function () {
		slideInner.removeClass('animate next');
		slideInner.find('.HailArticle').first().remove();
	}, 0);
}

function backHailArticle(event) {
	var slideInner = event.hailList.find('.slide-inner');
	slideInner.
		prepend(event.clone).
		addClass('animate');
	slideInner.addClass('back');
	setTimeout(function () {
		slideInner.removeClass('animate');
		slideInner.find('.HailArticle').last().remove();
		slideInner.removeClass('back');
	}, 0);
}


function HailPaginationDisplay(){
	//resize just happened, pixels changed
	$('.pagination-source').css('display','none');
	var totalListItems = 0;
	var pageNum = 1;
	var visibleItems = 10;
	$('.pagination li').each(function(e){
		var el = $(this);
		var hasTitle = el.find('a').attr('title');
		if(hasTitle === undefined || hasTitle === null){
			totalListItems++;
		}
		if(el.hasClass('active')){
			pageNum = el.find('a').text();
		}
	});

	var myWidth = $(window).width();

	if(myWidth > 600 ){
		visibleItems = 10;
	} else if(myWidth > 375 && myWidth < 599){
		visibleItems = 5;
	} else {
		visibleItems = 3;
	}
	$('.pagination-wrapper').bootpag({
		total: totalListItems,
		page: pageNum,
		maxVisible: visibleItems,
		leaps: true,
		firstLastUse: true,
		first: '<i class="fa fa-angle-double-left"></i>',
		last: '<i class="fa fa-angle-double-right"></i>',
		prev: '<i class="fa fa-angle-left"></i>',
		next: '<i class="fa fa-angle-right"></i>',
		wrapClass: 'pagination',
		activeClass: 'active',
		disabledClass: 'disabled',
		nextClass: 'next',
		prevClass: 'prev',
		lastClass: 'last',
		firstClass: 'first'
	}).on("page", function(event, num){
		document.location.href=$('.pagination-source li a.source-page-'+num)[0].href;
	});
	var visibleCount = 0;
	$('.pagination.bootpag li').each(function(e){
		if($(this).css("display") !== 'none'){
			visibleCount++;
		}
	});
	var paginationLength = visibleCount*43;
	$('.pagination.bootpag').css('width',paginationLength - 40);

	$(".pagination-wrapper").append("<div class='pagination-page-number'><div>Page "+pageNum+" of "+totalListItems+"</div></div>");
}

/** Pagination **/
if (typeof $.fn.bootpag !== 'undefined'){

	HailPaginationDisplay();
	$(window).resize(function() {
		HailPaginationDisplay();
	});
} else {
	$('.pagination-source').css('display','none');
}