<?php

namespace ServiceMaster\Modules\Shortcodes\Lib;

use ServiceMaster\Modules\Shortcodes\BackgroundSlider\BackgroundSlider;
use ServiceMaster\Modules\Shortcodes\ItemShowcase\ItemShowcase;
use ServiceMaster\Modules\Shortcodes\ItemShowcaseItem\ItemShowcaseItem;
use ServiceMaster\Modules\CallToAction\CallToAction;
use ServiceMaster\Modules\Counter\Countdown;
use ServiceMaster\Modules\Counter\Counter;
use ServiceMaster\Modules\Shortcodes\ElementsHolder\ElementsHolder;
use ServiceMaster\Modules\Shortcodes\ElementsHolderItem\ElementsHolderItem;
use ServiceMaster\Modules\GoogleMap\GoogleMap;
use ServiceMaster\Modules\Separator\Separator;
use ServiceMaster\Modules\PieCharts\PieChartBasic\PieChartBasic;
use ServiceMaster\Modules\PieCharts\PieChartDoughnut\PieChartDoughnut;
use ServiceMaster\Modules\PieCharts\PieChartDoughnut\PieChartPie;
use ServiceMaster\Modules\PieCharts\PieChartWithIcon\PieChartWithIcon;
use ServiceMaster\Modules\Shortcodes\AnimationsHolder\AnimationsHolder;
use ServiceMaster\Modules\Shortcodes\BlogSlider\BlogSlider;
use ServiceMaster\Modules\Shortcodes\CenteredSlider\CenteredSlider;
use ServiceMaster\Modules\Shortcodes\ComparisonPricingTables\ComparisonPricingTable;
use ServiceMaster\Modules\Shortcodes\ComparisonPricingTables\ComparisonPricingTablesHolder;
use ServiceMaster\Modules\Shortcodes\HorizontalTimeline\HorizontalTimeline;
use ServiceMaster\Modules\Shortcodes\HorizontalTimeline\HorizontalTimelineItem;
use ServiceMaster\Modules\Shortcodes\Icon\Icon;
use ServiceMaster\Modules\Shortcodes\IconProgressBar;
use ServiceMaster\Modules\Shortcodes\ImageGallery\ImageGallery;
use ServiceMaster\Modules\Shortcodes\InfoBox\InfoBox;
use ServiceMaster\Modules\Shortcodes\Process\ProcessHolder;
use ServiceMaster\Modules\Shortcodes\Process\ProcessItem;
use ServiceMaster\Modules\Shortcodes\SectionSubtitle\SectionSubtitle;
use ServiceMaster\Modules\Shortcodes\SectionTitle\SectionTitle;
use ServiceMaster\Modules\Shortcodes\TeamSlider\TeamSlider;
use ServiceMaster\Modules\Shortcodes\TeamSliderItem\TeamSliderItem;
use ServiceMaster\Modules\Shortcodes\CardSlider\CardSlider;
use ServiceMaster\Modules\Shortcodes\CardSliderItem\CardSliderItem;
use ServiceMaster\Modules\Shortcodes\TwitterSlider\TwitterSlider;
use ServiceMaster\Modules\Shortcodes\VerticalProgressBar\VerticalProgressBar;
use ServiceMaster\Modules\Shortcodes\VerticalSplitSlider\VerticalSplitSlider;
use ServiceMaster\Modules\Shortcodes\VerticalSplitSliderContentItem\VerticalSplitSliderContentItem;
use ServiceMaster\Modules\Shortcodes\VerticalSplitSliderLeftPanel\VerticalSplitSliderLeftPanel;
use ServiceMaster\Modules\Shortcodes\VerticalSplitSliderRightPanel\VerticalSplitSliderRightPanel;
use ServiceMaster\Modules\Shortcodes\VideoBanner\VideoBanner;
use ServiceMaster\Modules\Shortcodes\WorkingHours\WorkingHours;
use ServiceMaster\Modules\ProductList\ProductList;
use ServiceMaster\Modules\SocialShare\SocialShare;
use ServiceMaster\Modules\Team\Team;
use ServiceMaster\Modules\OrderedList\OrderedList;
use ServiceMaster\Modules\UnorderedList\UnorderedList;
use ServiceMaster\Modules\Message\Message;
use ServiceMaster\Modules\ProgressBar\ProgressBar;
use ServiceMaster\Modules\IconListItem\IconListItem;
use ServiceMaster\Modules\Tabs\Tabs;
use ServiceMaster\Modules\Tab\Tab;
use ServiceMaster\Modules\Shortcodes\TabSlider\TabSlider;
use ServiceMaster\Modules\Shortcodes\TabSlider\TabSliderItem;
use ServiceMaster\Modules\PricingTables\PricingTables;
use ServiceMaster\Modules\PricingTable\PricingTable;
use ServiceMaster\Modules\PricingTablesWithIcon\PricingTablesWithIcon;
use ServiceMaster\Modules\PricingTableWithIcon\PricingTableWithIcon;
use ServiceMaster\Modules\Accordion\Accordion;
use ServiceMaster\Modules\AccordionTab\AccordionTab;
use ServiceMaster\Modules\BlogList\BlogList;
use ServiceMaster\Modules\Shortcodes\Button\Button;
use ServiceMaster\Modules\Blockquote\Blockquote;
use ServiceMaster\Modules\CustomFont\CustomFont;
use ServiceMaster\Modules\Highlight\Highlight;
use ServiceMaster\Modules\VideoButton\VideoButton;
use ServiceMaster\Modules\Dropcaps\Dropcaps;
use ServiceMaster\Modules\Shortcodes\IconWithText\IconWithText;
use ServiceMaster\Modules\Shortcodes\InteractiveBox\InteractiveBox;
use ServiceMaster\Modules\Shortcodes\MiniTextSlider\MiniTextSlider;
use ServiceMaster\Modules\Shortcodes\MiniTextSliderItem\MiniTextSliderItem;
use ServiceMaster\Modules\ImageWithTextOver\ImageWithTextOver;
use ServiceMaster\Modules\RestaurantMenu\RestaurantMenu;
use ServiceMaster\Modules\RestaurantItem\RestaurantItem;
use ServiceMaster\Modules\Shortcodes\Playlist\Playlist;
use ServiceMaster\Modules\Shortcodes\PlaylistItem\PlaylistItem;
use ServiceMaster\Modules\Shortcodes\DeviceSlider\DeviceSlider;
use ServiceMaster\Modules\Shortcodes\MobileSlider\MobileSlider;
use ServiceMaster\Modules\Shortcodes\TableHolder\TableHolder;
use ServiceMaster\Modules\Shortcodes\TableItem\TableItem;
use ServiceMaster\Modules\Shortcodes\TableContentItem\TableContentItem;
use ServiceMaster\Modules\Shortcodes\CardsGallery\CardsGallery;
use ServiceMaster\Modules\Shortcodes\AdvancedSlider\AdvancedSlider;
use ServiceMaster\Modules\Shortcodes\AdvancedSliderItem\AdvancedSliderItem;
use ServiceMaster\Modules\Shortcodes\ReservationForm\ReservationForm;
use ServiceMaster\Modules\Shortcodes\TextMarquee\TextMarquee;

/**
 * Class ShortcodeLoader
 */
class ShortcodeLoader {
	/**
	 * @var private instance of current class
	 */
	private static $instance;
	/**
	 * @var array
	 */
	private $loadedShortcodes = array();

	/**
	 * Private constuct because of Singletone
	 */
	private function __construct() {
	}

	/**
	 * Private sleep because of Singletone
	 */
	private function __wakeup() {
	}

	/**
	 * Private clone because of Singletone
	 */
	private function __clone() {
	}

	/**
	 * Returns current instance of class
	 * @return ShortcodeLoader
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			return new self;
		}

		return self::$instance;
	}

	/**
	 * Adds new shortcode. Object that it takes must implement ShortcodeInterface
	 *
	 * @param ShortcodeInterface $shortcode
	 */
	private function addShortcode(ShortcodeInterface $shortcode) {
		if (!array_key_exists($shortcode->getBase(), $this->loadedShortcodes)) {
			$this->loadedShortcodes[$shortcode->getBase()] = $shortcode;
		}
	}

	/**
	 * Adds all shortcodes.
	 *
	 * @see ShortcodeLoader::addShortcode()
	 */
	private function addShortcodes() {
		$this->addShortcode(new BackgroundSlider());
		$this->addShortcode(new ElementsHolder());
		$this->addShortcode(new ElementsHolderItem());
		$this->addShortcode(new Team());
		$this->addShortcode(new TeamSlider());
		$this->addShortcode(new TeamSliderItem());
		$this->addShortcode(new Icon());
		$this->addShortcode(new CallToAction());
		$this->addShortcode(new OrderedList());
		$this->addShortcode(new UnorderedList());
		$this->addShortcode(new Message());
		$this->addShortcode(new Counter());
		$this->addShortcode(new Countdown());
		$this->addShortcode(new ProgressBar());
		$this->addShortcode(new IconListItem());
		$this->addShortcode(new Tabs());
		$this->addShortcode(new Tab());
		$this->addShortcode(new PricingTables());
		$this->addShortcode(new PricingTable());
		$this->addShortcode(new PricingTablesWithIcon());
		$this->addShortcode(new PricingTableWithIcon());
		$this->addShortcode(new PieChartBasic());
		$this->addShortcode(new PieChartPie());
		$this->addShortcode(new PieChartDoughnut());
		$this->addShortcode(new PieChartWithIcon());
		$this->addShortcode(new Accordion());
		$this->addShortcode(new AccordionTab());
		$this->addShortcode(new BlogList());
		$this->addShortcode(new Button());
		$this->addShortcode(new Blockquote());
		$this->addShortcode(new CustomFont());
		$this->addShortcode(new Highlight());
		$this->addShortcode(new ImageGallery());
		$this->addShortcode(new GoogleMap());
		$this->addShortcode(new Separator());
		$this->addShortcode(new VideoButton());
		$this->addShortcode(new Dropcaps());
		$this->addShortcode(new IconWithText());
		$this->addShortcode(new InteractiveBox());
		$this->addShortcode(new SocialShare());
		$this->addShortcode(new VideoBanner());
		$this->addShortcode(new AnimationsHolder());
		$this->addShortcode(new SectionTitle());
		$this->addShortcode(new SectionSubtitle());
		$this->addShortcode(new InfoBox());
		$this->addShortcode(new ProcessHolder());
		$this->addShortcode(new ProcessItem());
		$this->addShortcode(new ComparisonPricingTablesHolder());
		$this->addShortcode(new ComparisonPricingTable());
		$this->addShortcode(new HorizontalTimeline());
		$this->addShortcode(new HorizontalTimelineItem());
		$this->addShortcode(new VerticalProgressBar());
		$this->addShortcode(new IconProgressBar());
		$this->addShortcode(new WorkingHours());
		$this->addShortcode(new BlogSlider());
		$this->addShortcode(new TwitterSlider());
		$this->addShortcode(new CenteredSlider());
		$this->addShortcode(new VerticalSplitSlider());
		$this->addShortcode(new VerticalSplitSliderLeftPanel());
		$this->addShortcode(new VerticalSplitSliderRightPanel());
		$this->addShortcode(new VerticalSplitSliderContentItem());
		$this->addShortcode(new MiniTextSlider());
		$this->addShortcode(new MiniTextSliderItem());
		$this->addShortcode(new TabSlider());
		$this->addShortcode(new TabSliderItem());
		$this->addShortcode(new CardSlider());
		$this->addShortcode(new CardSliderItem());
		$this->addShortcode(new ImageWithTextOver());
		$this->addShortcode(new RestaurantMenu());
		$this->addShortcode(new RestaurantItem());
		$this->addShortcode(new Playlist());
		$this->addShortcode(new PlaylistItem());
		$this->addShortcode(new DeviceSlider());
		$this->addShortcode(new MobileSlider());
		$this->addShortcode(new TableHolder());
		$this->addShortcode(new TableItem());
		$this->addShortcode(new TableContentItem());
		$this->addShortcode(new CardsGallery());
		$this->addShortcode(new AdvancedSlider());
		$this->addShortcode(new AdvancedSliderItem());
		$this->addShortcode(new ReservationForm());
        $this->addShortcode(new ItemShowcase());
        $this->addShortcode(new ItemShowcaseItem());
        $this->addShortcode(new TextMarquee());
		if (servicemaster_mikado_is_woocommerce_installed()) {
			$this->addShortcode(new ProductList());
		}

	}

	/**
	 * Calls ShortcodeLoader::addShortcodes and than loops through added shortcodes and calls render method
	 * of each shortcode object
	 */
	public function load() {
		$this->addShortcodes();

		foreach ($this->loadedShortcodes as $shortcode) {
			add_shortcode($shortcode->getBase(), array($shortcode, 'render'));
		}

	}
}

$shortcodeLoader = ShortcodeLoader::getInstance();
$shortcodeLoader->load();