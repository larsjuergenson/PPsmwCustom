<?php

namespace PP\SMW\Helpers;

use SMW\MediaWiki\Renderer\WikitextTemplateRenderer;
use SMW\ApplicationFactory;


/**
  * Encapsulates the rendering of template for use in the formatters.
  *
  * Not sure why SMW does not provide a class like this. The "native" 
  * formatters all handle WikitextTemplateRenderer directly, which
  * makes reading the code unecessarily difficult unless you know how that
  * class works.
  *
  * The main entry point is the static TemplateHelper::constructTemplateCall()
  * function. Normally, you should not need to use anything else.
  *
  */

class TemplateHelper {
	/**
	 * @var WikitextTemplateRenderer;
	 */

	private $renderer;

	/**
	 * TemplateHelper constructor.
	 *
	 * @param WikitextTemplateRenderer $renderer is expected to be a fresh 
	 *                                           instance, with no params set.
	 */
	function __construct(WikitextTemplateRenderer $renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * Constructs a template call with the provided parameters.
	 *
	 * @param string $template_name
	 * @param array  $params
	 *
	 * @return string The resulting template call.
	 *
	 * Note: This method takes its name from the WikitextTemplateRenderer
	 *       method it invokes. Despite its name, does not actually 'render' 
	 *       the template. 
	 *
	 *       Instead, it just creates a template call that, when interpreted as 
	 *       wikitext, will transclude the template (or call the parser 
	 *       function) with the provided arguments. The function also does not 
	 *       test whether the template or parser function exists.
	 */

	private function render (
		string $template_name,  
		array $params=[] 
	) : string {

		foreach ($params as $key => $val) {
			$this->renderer->addField($key, $val);
		}

		$this->renderer->packFieldsForTemplate($template_name);

		return $this->renderer->render();
	}

	/*
	 * Factory method. Provides a fresh WikitextTemplate Renderer instance.
	 *
	 * In usual circumstances, it should not be necessary to call this directly,
	 * unless you are creating A LOT of template calls in one go and want to 
	 * avoid the (small) memory cost of creating a TemplateHelper / 
	 * WikitextTemplateRenderer instance per call.
	 *
	 */
	public static function create() {
		$renderer = ApplicationFactory::getInstance()
		              ->newMwCollaboratorFactory()
		              ->newWikitextTemplateRenderer();

		return new TemplateHelper( $renderer );
	}


	/*
	 * Constructs a template call for inclusion in wikipages.
	 *
	 * The $params argument should be an array that has string keys for named
	 * arguments, and numeric keys for unnamed arguments, starting at zero (e.g. 
	 * $params[0] = 'foo' for the first argument, $params[1] = 'bar' for the
	 * second, etc.)
	 *
	 * @param string $template_name 
	 * @param array  $params
	 */

	public static function getTemplateCall(
		string $template_name, 
		array $params 
	) : string {
		return self::create()->render( $template_name, $params );
	}

}