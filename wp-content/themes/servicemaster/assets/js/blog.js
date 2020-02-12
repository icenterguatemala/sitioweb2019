(function ($) {
	"use strict";


	var blog = {};
	mkd.modules.blog = blog;

	blog.mkdInitAudioPlayer = mkdInitAudioPlayer;
	blog.mkdInitBlogMasonry = mkdInitBlogMasonry;
	blog.mkdInitBlogMasonryGallery = mkdInitBlogMasonryGallery;
	blog.mkdInitBlogSplit = mkdInitBlogSplit;
	blog.mkdInitBlogLoadMore = mkdInitBlogLoadMore;

	blog.mkdOnDocumentReady = mkdOnDocumentReady;
	blog.mkdOnWindowLoad = mkdOnWindowLoad;
	blog.mkdOnWindowResize = mkdOnWindowResize;
	blog.mkdOnWindowScroll = mkdOnWindowScroll;

	$(document).ready(mkdOnDocumentReady);
	$(window).load(mkdOnWindowLoad);
	$(window).resize(mkdOnWindowResize);
	$(window).scroll(mkdOnWindowScroll);

	/*
	 All functions to be called on $(document).ready() should be in this function
	 */
	function mkdOnDocumentReady() {
		mkdInitAudioPlayer();
		mkdInitBlogMasonry();
		mkdInitBlogLoadMore();
		mkdInitBlogSplit();
		mkdInitBlogMasonryGallery();
	}

	/*
	 All functions to be called on $(window).load() should be in this function
	 */
	function mkdOnWindowLoad() {
		mkdGetInfiniteScrollTriggerPosition();
	}

	/*
	 All functions to be called on $(window).resize() should be in this function
	 */
	function mkdOnWindowResize() {

	}

	/*
	 All functions to be called on $(window).scroll() should be in this function
	 */
	function mkdOnWindowScroll() {

	}


	function mkdInitAudioPlayer() {

		var players = $('audio.mkd-blog-audio');

		players.mediaelementplayer({
			audioWidth: '100%'
		});
	}

	function mkdInitBlogSplit() {
		if ($('.mkd-blog-holder.mkd-blog-type-split').length) {
			var container = $('.mkd-blog-holder.mkd-blog-type-split');

			container.waitForImages(function () {
				container.isotope({
					layoutMode: 'vertical'
				});
			});

			container.find('article').appear(function () {
				$(this).addClass('appeared');
			});
		}
	}

	function mkdInitBlogMasonry() {
		if ($('.mkd-blog-holder.mkd-blog-type-masonry').length) {
			var container = $('.mkd-blog-holder.mkd-blog-type-masonry');

			container.waitForImages(function () {
				container.isotope({
					itemSelector: 'article',
					resizable: false,
					masonry: {
						columnWidth: '.mkd-blog-masonry-grid-sizer',
						gutter: '.mkd-blog-masonry-grid-gutter'
					}
				});
			});

			var filters = $('.mkd-filter-blog-holder');
			$('.mkd-filter').click(function () {
				var filter = $(this);
				var selector = filter.attr('data-filter');
				filters.find('.mkd-active').removeClass('mkd-active');
				filter.addClass('mkd-active');
				container.isotope({filter: selector});
				return false;
			});
		}
	}

	function mkdInitBlogMasonryGallery() {

		var container = $('.mkd-blog-holder.mkd-blog-type-masonry-gallery');
		if (container.length) {
			container.each(function () {
				var thisBlogList = $(this);

				mkdBlogResizeMasonryGallery(thisBlogList);

				mkdBlogInitMasonryGallery(thisBlogList);
				$(window).resize(function () {
					mkdBlogResizeMasonryGallery(thisBlogList);
					mkdBlogInitMasonryGallery(thisBlogList);
				});

				if (!mkd.htmlEl.hasClass('touch')) {
					$(window).load(function(){
						var article = thisBlogList.find('article'),
							offset = article.first().offset().top,
							cycle = 0,
						    n = 0;

						article.each(function(){
						    var currentArticle = $(this);
						    if (currentArticle.offset().top == offset) {
						        cycle ++;
						    }
						});
						
						article.not('mkd-appeared').appear(function(){
						    var currentArticle = $(this);

						    if (n == cycle) {
						        n = 0;
						    }

						    setTimeout(function(){
						        currentArticle.addClass('mkd-appeared')
						    }, n * 200);

						    n++;
						},{accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
					});
				}
			});
		}

	}


	function mkdBlogInitMasonryGallery(container) {
		container.waitForImages(function () {
			container.isotope({
				itemSelector: 'article',
				resizable: false,
				layoutMode: 'packery',
				packery: {
					columnWidth: '.mkd-blog-masonry-gallery-grid-sizer'
				}
			});
			container.addClass('mkd-appeared');
		});
	}

	function mkdBlogResizeMasonryGallery(container) {
		var size = container.find('.mkd-blog-masonry-gallery-grid-sizer').width();

		var defaultMasonryItem = container.find('.mkd-post-size-square');
		var largeWidthMasonryItem = container.find('.mkd-post-size-large-width');
		var largeHeightMasonryItem = container.find('.mkd-post-size-large-height');
		var largeWidthHeightMasonryItem = container.find('.mkd-post-size-large-width-height');

		defaultMasonryItem.css('height', size);
		largeWidthMasonryItem.css('height', size);
		largeHeightMasonryItem.css('height', Math.round(2 * size));

		if (mkd.windowWidth > 600) {
			largeWidthHeightMasonryItem.css('height', Math.round(2 * size));
		} else {
			largeWidthHeightMasonryItem.css('height', size);
		}
	}

	/*
	 Initialize load more ajax pagination
	 */
	function mkdInitBlogLoadMore() {
		var blogHolder = $('.mkd-blog-holder.mkd-blog-load-more');

		var loadMoreButton;

		if (blogHolder.hasClass('mkd-blog-type-masonry')) {
			loadMoreButton = blogHolder.next().find('.mkd-btn');
		}
		else {
			loadMoreButton = blogHolder.find('.mkd-load-more-ajax-pagination .mkd-btn');
		}

		// on click initialize ajax pagination
		loadMoreButton.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			mkdInitAjaxPagination(blogHolder, loadMoreButton);

		});
	}

	/*
	 Initialize infinite scroll ajax pagination
	 */

	function mkdGetInfiniteScrollTriggerPosition() {
		var blogHolder = $('.mkd-blog-holder.mkd-blog-infinite-scroll');
		var trigger = $('.mkd-infinite-scroll-trigger');

		if (trigger.length) {
			mkd.window.scroll(function () {
				if (!blogHolder.hasClass('mkd-ajax-started') && (mkd.windowHeight + mkd.window.scrollTop() > trigger.offset().top)) {
					blogHolder.addClass('mkd-ajax-started');
					mkdInitAjaxPagination(blogHolder, '');
				}
			});
		}
	}

	function mkdInitAjaxPagination(container, loadMoreButton) { //if not load more pagination, proceed empty string
		var nextPage;
		var maxNumPages;
		var loadMoreDatta = getBlogAjaxPaginationData(container);
		maxNumPages = container.data('max-pages');
		nextPage = loadMoreDatta.nextPage;

		if (nextPage <= maxNumPages) {
			var ajaxData = setBlogLoadMoreAjaxData(loadMoreDatta);
			$.ajax({
				type: 'POST',
				data: ajaxData,
				url: MikadoAjaxUrl,
				success: function (data) {
					nextPage++;
					container.data('next-page', nextPage);
					var response = $.parseJSON(data);
					var responseHtml = response.html;
					container.waitForImages(function () {
						if (container.hasClass('mkd-blog-type-masonry')) {
							container.append(responseHtml).isotope('reloadItems').isotope({sortBy: 'original-order'});
							mkdInitBlogMasonry();
						}
						else {
							container.find('article:last').after(responseHtml); // Append the new content
						}
						setTimeout(function () {
							mkd.modules.blog.mkdInitAudioPlayer();
							mkd.modules.common.mkdSlickSlider();
							mkd.modules.common.mkdFluidVideo();
						}, 400);
						container.removeClass('mkd-ajax-started');
					});
				}
			});
		}

		if (nextPage === maxNumPages) {
			if (loadMoreButton !== '') {
				loadMoreButton.hide();
			}
		}

	}

	function getBlogAjaxPaginationData(container) {

		var returnValue = {};

		returnValue.nextPage = '';
		returnValue.number = '';
		returnValue.category = '';
		returnValue.blogType = '';
		returnValue.archiveCategory = '';
		returnValue.archiveAuthor = '';
		returnValue.archiveTag = '';
		returnValue.archiveDay = '';
		returnValue.archiveMonth = '';
		returnValue.archiveYear = '';

		if (typeof container.data('next-page') !== 'undefined' && container.data('next-page') !== false) {
			returnValue.nextPage = container.data('next-page');
		}
		if (typeof container.data('post-number') !== 'undefined' && container.data('post-number') !== false) {
			returnValue.number = container.data('post-number');
		}
		if (typeof container.data('category') !== 'undefined' && container.data('category') !== false) {
			returnValue.category = container.data('category');
		}
		if (typeof container.data('blog-type') !== 'undefined' && container.data('blog-type') !== false) {
			returnValue.blogType = container.data('blog-type');
		}
		if (typeof container.data('archive-category') !== 'undefined' && container.data('archive-category') !== false) {
			returnValue.archiveCategory = container.data('archive-category');
		}
		if (typeof container.data('archive-author') !== 'undefined' && container.data('archive-author') !== false) {
			returnValue.archiveAuthor = container.data('archive-author');
		}
		if (typeof container.data('archive-tag') !== 'undefined' && container.data('archive-tag') !== false) {
			returnValue.archiveTag = container.data('archive-tag');
		}
		if (typeof container.data('archive-day') !== 'undefined' && container.data('archive-day') !== false) {
			returnValue.archiveDay = container.data('archive-day');
		}
		if (typeof container.data('archive-month') !== 'undefined' && container.data('archive-month') !== false) {
			returnValue.archiveMonth = container.data('archive-month');
		}
		if (typeof container.data('archive-year') !== 'undefined' && container.data('archive-year') !== false) {
			returnValue.archiveYear = container.data('archive-year');
		}

		return returnValue;

	}

	function setBlogLoadMoreAjaxData(container) {

		var returnValue = {
			action: 'servicemaster_mikado_blog_load_more',
			nextPage: container.nextPage,
			number: container.number,
			category: container.category,
			blogType: container.blogType,
			archiveCategory: container.archiveCategory,
			archiveAuthor: container.archiveAuthor,
			archiveTag: container.archiveTag,
			archiveDay: container.archiveDay,
			archiveMonth: container.archiveMonth,
			archiveYear: container.archiveYear
		};

		return returnValue;
	}


})(jQuery);