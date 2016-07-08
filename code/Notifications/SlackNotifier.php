<?php

/**
 * A Slack provider for Hail using the maknz/slack package
 * @see https://github.com/maknz/slack
 */
class SlackNotifier implements HailNotifier {

	/**
	 * The Slack Hooks URL
	 * @see https://my.slack.com/services/new/incoming-webhook
	 * @var string
	 */
	private static $hooks_url;

	/**
	 * The default username for your bot
	 * @var string
	 */
	private static $username;

	/**
	 * The default channel that messages will be sent to
	 * @var string
	 */
	private static $channel;

	/**
	 * The default icon that messages will be sent with, either :emoji: or a URL to an image
	 * @var string
	 */
	private static $icon;

	/**
	 * Whether names like @regan or #accounting should be linked in the message (defaults to false)
	 * @var boolean
	 */
	private static $link_names = false;

	/**
	 * Whether Slack should unfurl text-based URLs (defaults to false)
	 * @var boolean
	 */
	private static $unfurl_links = false;

	/**
	 * Whether Slack should unfurl media-based URLs, like tweets or Youtube videos (defaults to true)
	 * @var boolean
	 */
	private static $unfurl_media = true;

	/**
	 * Whether markdown should be parsed in messages, or left as plain text (defaults to true)
	 * @var boolean
	 */
	private static $allow_markdown = true;

	/**
	 * Which attachment fields should have markdown parsed (defaults to none)
	 * @var array
	 */
	private static $markdown_in_attachments = null;

	/**
	 * The Slack client
	 * @var Maknz\Slack\Client
	 */
	private $client;

	public function __construct() {

		if(!class_exists('Maknz\Slack\Client')) {
			user_error('A required class "Maknz\Slack\Client" does not exist');
		}

		$settings = array(
			'username' => Config::inst()->get('SlackNotifier', 'username'),
			'channel' => Config::inst()->get('SlackNotifier', 'channel'),
			'icon' => Config::inst()->get('SlackNotifier', 'icon'),
			'link_names' => Config::inst()->get('SlackNotifier', 'link_names'),
			'unfurl_links' => Config::inst()->get('SlackNotifier', 'unfurl_links'),
			'unfurl_media' => Config::inst()->get('SlackNotifier', 'unfurl_media'),
			'allow_markdown' => Config::inst()->get('SlackNotifier', 'allow_markdown'),
			'markdown_in_attachments' => Config::inst()->get('SlackNotifier', 'markdown_in_attachments'),
		);

		$this->client = new Maknz\Slack\Client(Config::inst()->get('SlackNotifier', 'hooks_url'), $settings);
	}

	/**
	 * Sends a notification via Slack
	 * @param string $message 
	 */
	public function sendNotification($message) {

		$config = SiteConfig::current_site_config();

		$message = $config->Title . ' (' . Director::absoluteBaseURL() . ') - ' . $message;

		$this->client->send($message);

	}

}