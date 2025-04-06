<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Service\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\SWJProjects\Administrator\RouteHelper;
use Joomla\Registry\Registry;
use function base64_encode;
use function property_exists;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * SWJProjects Component HTML Helper
 *
 * @since  4.0.0
 */
class Icon
{
	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 * @param   boolean   $legacy    True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the create item link
	 *
	 * @since  4.0.0
	 */
	public function create($category, $params, $attribs = [], $legacy = false)
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_swjprojects&task=item.add&return=' . base64_encode($uri) . '&a_id=0&catid=' . $category->id;

		$text = '';

		if ($params->get('show_icons')) {
			$text .= '<span class="icon-plus icon-fw" aria-hidden="true"></span>';
		}

		$text .= Text::_('COM_SWJProjects_ITEM_ADD');

		// Add the button classes to the attribs array
		if (isset($attribs['class'])) {
			$attribs['class'] .= ' btn btn-primary';
		} else {
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $button;
	}

	/**
	 * Display an edit icon for the article.
	 *
	 * This icon will not display in a popup window, nor if the article is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML for the article edit icon.
	 *
	 * @since  4.0.0
	 */
	public function edit($article, $params, $attribs = [], $legacy = false)
	{
		$user = Factory::getApplication()->getIdentity();
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup')) {
			return '';
		}

		// Ignore if the state is negative (trashed).
		if (!\in_array($article->state, [Workflow::CONDITION_UNPUBLISHED, Workflow::CONDITION_PUBLISHED])) {
			return '';
		}

		// Show checked_out icon if the article is checked out by a different user
		if (
			property_exists($article, 'checked_out')
			&& property_exists($article, 'checked_out_time')
			&& !\is_null($article->checked_out)
			&& $article->checked_out != $user->get('id')
		) {
			$checkoutUser = Factory::getApplication()->getIdentity($article->checked_out);
			$date         = HTMLHelper::_('date', $article->checked_out_time);
			$tooltip      = Text::sprintf('COM_SWJProjects_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br> ' . $date;

			$text = LayoutHelper::render('joomla.SWJProjects.icons.edit_lock', ['article' => $article, 'tooltip' => $tooltip, 'legacy' => $legacy]);

			$attribs['aria-describedby'] = 'editarticle-' . (int) $article->id;
			$output                      = HTMLHelper::_('link', '#', $text, $attribs);

			return $output;
		}

		$SWJProjectsUrl = RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language);
		$url        = $SWJProjectsUrl . '&task=article.edit&a_id=' . $article->id . '&return=' . base64_encode($uri);

		if ($article->state == Workflow::CONDITION_UNPUBLISHED) {
			$tooltip = Text::_('COM_SWJProjects_EDIT_UNPUBLISHED_ARTICLE');
		} else {
			$tooltip = Text::_('COM_SWJProjects_EDIT_PUBLISHED_ARTICLE');
		}

		$text = LayoutHelper::render('joomla.SWJProjects.icons.edit', ['article' => $article, 'tooltip' => $tooltip, 'legacy' => $legacy]);

		$attribs['aria-describedby'] = 'editarticle-' . (int) $article->id;
		$output                      = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Method to generate a link to print an article
	 *
	 * @param   Registry  $params  The item parameters
	 * @param   boolean   $legacy  True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @since  4.0.0
	 */
	public function print_screen($params, $legacy = false)
	{
		$text = LayoutHelper::render('joomla.SWJProjects.icons.print_screen', ['params' => $params, 'legacy' => $legacy]);

		return '<button type="button" onclick="window.print();return false;">' . $text . '</button>';
	}
}
