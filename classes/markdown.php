<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * Markdown  -  A text-to-HTML conversion tool for web writers
 *
 * Ported to Kohana by Lillem4n (lilleman@larvit.se) at the
 * Pajas project (http://larvit.se/pajas)
 * Use like this:
 *
 *   $html = Markdown::transform($text);
 *
 * Ported from: PHP Markdown
 * Copyright (c) 2004-2009 Michel Fortin
 * <http://michelf.com/projects/php-markdown/>
 *
 * Original Markdown
 * Copyright (c) 2004-2006 John Gruber
 * <http://daringfireball.net/projects/markdown/>
 */

// Tip: Run Markdown->transform() to transform text to html
class Markdown
{

	// Regex to match balanced [brackets].
	// Needed to insert a maximum bracked depth while converting to PHP.
	private static $nested_brackets_depth = 6;
	private static $nested_brackets_re;

	private static $nested_url_parenthesis_depth = 4;
	private static $nested_url_parenthesis_re;

	// Table of hash values for escaped characters:
	private static $escape_chars = '\`*_{}[]()>#+-.!';
	private static $escape_chars_re;

	// Change to ">" for HTML output.
	private static $empty_element_suffix = ' />';
	private static $tab_width            = 4;

	// Change to `TRUE` to disallow markup or entities.
	private static $no_markup   = FALSE;
	private static $no_entities = FALSE;

	// Predefined urls and titles for reference links and images.
	private static $predef_urls   = array();
	private static $predef_titles = array();

	// Internal hashes used during transformation.
	private static $urls        = array();
	private static $titles      = array();
	private static $html_hashes = array();

	// Status flag to avoid invalid nesting.
	private static $in_anchor   = FALSE;


	/**
	 *
	 * Called before the transformation process starts to setup parser
	 * states
	 *
	 */
	private static function setup()
	{
		// Clear global hashes.
		self::$urls        = self::$predef_urls;
		self::$titles      = self::$predef_titles;
		self::$html_hashes = array();

		$in_anchor = FALSE;
	}

	/**
	 *
	 * Called after the transformation process to clear any variable
	 * which may be taking up memory unnecessarly.
	 *
	 */
	private static function teardown()
	{
		self::$urls        = array();
		self::$titles      = array();
		self::$html_hashes = array();
	}

	/**
	 *
	 * Main function. Performs some preprocessing on the input text
	 * and pass it through the document gamut.
	 *
	 */
	public static function transform($text)
	{

		/**
		 * From previous constructor
		 */
		self::_init_detab();
		self::prepare_italics_and_bold();

		self::$nested_brackets_re =
			str_repeat('(?>[^\[\]]+|\[', self::$nested_brackets_depth).
			str_repeat('\])*', self::$nested_brackets_depth);

		self::$nested_url_parenthesis_re =
			str_repeat('(?>[^()\s]+|\(', self::$nested_url_parenthesis_depth).
			str_repeat('(?>\)))*', self::$nested_url_parenthesis_depth);

		self::$escape_chars_re = '['.preg_quote(self::$escape_chars).']';

		// Sort document, block, and span gamut in ascendent priority order.
		asort(self::$document_gamut);
		asort(self::$block_gamut);
		asort(self::$span_gamut);
		/**
		 * End of constructor stuff
		 */

		self::setup();

		// Remove UTF-8 BOM and marker character in input, if present.
		$text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

		// Standardize line endings:
		//   DOS to Unix and Mac to Unix
		$text = preg_replace('{\r\n?}', "\n", $text);

		// Make sure $text ends with a couple of newlines:
		$text .= "\n\n";

		// Convert all tabs to spaces.
		$text = self::detab($text);

		// Turn block-level HTML blocks into hash entries
		$text = self::hash_HTML_blocks($text);

		// Strip any lines consisting only of spaces and tabs.
		// This makes subsequent regexen easier to write, because we can
		// match consecutive blank lines with /\n+/ instead of something
		// contorted like /[ ]*\n+/ .
		$text = preg_replace('/^[ ]+$/m', '', $text);

		// Run document gamut methods.
		foreach (self::$document_gamut as $method => $priority)
		{
			$text = self::$method($text);
		}

		self::teardown();

		return $text."\n";
	}

	// Strip link definitions, store in hashes.
	private static $document_gamut = array(
		'strip_link_definitions' => 20,
		'run_basic_block_gamut'  => 30,
	);

	/**
	 *
	 * Strips link definitions from text, stores the URLs and titles in
	 * hash references.
	 *
	 */
	private static function strip_link_definitions($text)
	{
		$less_than_tab = self::$tab_width - 1;

		// Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	// id = $1
							  [ ]*
							  \n?         # maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>     # url = $2
							|
							  (\S+?)      # url = $3
							)
							  [ ]*
							  \n?         # maybe one newline
							  [ ]*
							(?:
								(?<=\s)     # lookbehind for whitespace
								["(]
								(.*?)       # title = $4
								[")]
								[ ]*
							)?	// title is optional
							(?:\n+|\Z)
			}xm',
			array(__CLASS__, '_strip_link_definitions_callback'),
			$text
		);
		return $text;
	}
	private static function _strip_link_definitions_callback($matches)
	{
		$link_id                = strtolower($matches[1]);
		$url                    = $matches[2] == '' ? $matches[3] : $matches[2];
		self::$urls[$link_id]   = $url;
		self::$titles[$link_id] =& $matches[4];
		return ''; // String that will replace the block
	}


	private static function hash_HTML_blocks($text)
	{
		if (self::$no_markup)  return $text;

		$less_than_tab = self::$tab_width - 1;

		/**
		 * Hashify HTML blocks:
		 * We only want to do this for block-level HTML tags, such as headers,
		 * lists, and tables. That's because we still want to wrap <p>s around
		 * "paragraphs" that are wrapped in non-block-level tags, such as anchors,
		 * phrase emphasis, and spans. The list of tags we're looking for is
		 * hard-coded:
		 *
		 * *  List "a" is made of tags which can be both inline or block-level.
		 *    These will be treated block-level when the start tag is alone on
		 *    its line, otherwise they're not matched here and will be taken as
		 *    inline later.
		 * *  List "b" is made of tags which are always block-level;
		 */
		$block_tags_a_re = 'ins|del';
		$block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
						   'script|noscript|form|fieldset|iframe|math';

		// Regular expression for the content of a block tag.
		$nested_tags_level = 4;
		$attr = '
			(?>           # optional tag attributes
			  \s          # starts with whitespace
			  (?>
				[^>"/]+     # text outside quotes
			  |
				/+(?!>)     # slash not followed by ">"
			  |
				"[^"]*"     # text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'  # text inside single quotes (tolerate ">")
			  )*
			)?
			';
		$content =
			str_repeat('
				(?>
				  [^<]+     # content without tag
				|
				  <\2       # nested opening tag
					'.$attr.' # attributes
					(?>
					  />
					|
					  >', $nested_tags_level).	// end of opening tag
					  '.*?'.					// last level nested tag content
			str_repeat('
					  </\2\s*> # closing nested tag
					)
				  |
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*',
				$nested_tags_level);
		$content2 = str_replace('\2', '\3', $content);

		// First, look for nested blocks, e.g.:
		// 	<div>
		// 		<div>
		// 		tags for inner block must be indented.
		// 		</div>
		// 	</div>
		//
		// The outermost tags must start at the left margin for this to match, and
		// the inner nested divs must be indented.
		// We need to do this before the next, more liberal match, because the next
		// match will start at the first `<div>` and stop at the first `</div>`.
		$text = preg_replace_callback('{(?>
			(?>
				(?<=\n\n)   # Starting after a blank line
				|           # or
				\A\n?       # the beginning of the doc
			)
			(             # save in $1

			      # Match from `\n<tag>` to `</tag>\n`, handling nested tags
			      # in between.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.') # start tag = $2
						'.$attr.'>              # attributes followed by > and \n
						'.$content.'            # content, support nesting
						</\2>                   # the matching end tag
						[ ]*                    # trailing spaces/tabs
						(?=\n+|\Z)              # followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.') # start tag = $3
						'.$attr.'>[ ]*\n        # attributes followed by >
						'.$content2.'           # content, support nesting
						</\3>                   # the matching end tag
						[ ]*                    # trailing spaces/tabs
						(?=\n+|\Z)              # followed by a newline or end of document

			| # Special case just for <hr />. It was easier to make a special
			  # case than to make the other regex more complicated.

						[ ]{0,'.$less_than_tab.'}
						<(hr)           # start tag = $2
						'.$attr.'       # attributes
						/?>             # the matching end tag
						[ ]*
						(?=\n{2,}|\Z)   # followed by a blank line or end of document

			| # Special case for standalone HTML comments:

					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z) # followed by a blank line or end of document

			| # PHP and ASP-style processor instructions (<? and <%)

					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])      # $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)  # followed by a blank line or end of document

			)
			)}Sxmi',
			array(__CLASS__, '_hash_HTML_blocks_callback'),
			$text
		);

		return $text;
	}
	private static function _hash_HTML_blocks_callback($matches)
	{
		$text = $matches[1];
		$key  = self::hash_block($text);
		return "\n\n$key\n\n";
	}


	/**
	 *
	 * Called whenever a tag must be hashed when a function insert an atomic
	 * element in the text stream. Passing $text to through this function gives
	 * a unique text-token which will be reverted back when calling unhash.
	 *
	 * The $boundary argument specify what character should be used to surround
	 * the token. By convension, "B" is used for block elements that needs not
	 * to be wrapped into paragraph tags at the end, ":" is used for elements
	 * that are word separators and "X" is used in the general case.
	 */
	private static function hash_part($text, $boundary = 'X')
	{
		// Swap back any tag hash found in $text so we do not have to `unhash`
		// multiple times at the end.
		$text = self::unhash($text);

		// Then hash the block.
		static $i                = 0;
		$key                     = $boundary."\x1A".++$i.$boundary;
		self::$html_hashes[$key] = $text;
		return $key; // String that will replace the tag.
	}

	/**
	 *
	 * Shortcut function for hash_part with block-level boundaries.
	 *
	 */
	private static function hash_block($text)
	{
		return self::hash_part($text, 'B');
	}

	/**
	 *
	 * These are all the transformations that form block-level
	 * tags like paragraphs, headers, and list items.
	 *
	 */
	private static $block_gamut = array(
		'do_headers'          => 10,
		'do_horizontal_rules' => 20,

		'do_lists'            => 40,
		'do_code_blocks'      => 50,
		'do_block_quotes'     => 60,
	);

	/**
	 * Run block gamut tranformations.
	 */
	private static function run_block_gamut($text)
	{
		/**
		 * We need to escape raw HTML in Markdown source before doing anything
		 * else. This need to be done for each block, and not only at the
		 * begining in the Markdown function since hashed blocks can be part of
		 * list items and could have been indented. Indented blocks would have
		 * been seen as a code block in a previous pass of hash_HTML_blocks.
		 */
		$text = self::hash_HTML_blocks($text);

		return self::run_basic_block_gamut($text);
	}

	/**
	 * Run block gamut tranformations, without hashing HTML blocks. This is
	 * useful when HTML blocks are known to be already hashed, like in the first
	 * whole-document pass.
	 */
	private static function run_basic_block_gamut($text)
	{
		foreach (self::$block_gamut as $method => $priority)
		{
			$text = self::$method($text);
		}

		// Finally form paragraph and restore hashed blocks.
		$text = self::form_paragraphs($text);

		return $text;
	}


	private static function do_horizontal_rules($text)
	{
		// Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}  # Leading space
				([-*_])    # $1: First marker
				(?>        # Repeated marker group
					[ ]{0,2} # Zero, one, or two spaces.
					\1       # Marker character
				){2,}      # Group repeated at least twice
				[ ]*       # Tailing spaces
				$          # End of line.
			}mx',
			"\n".self::hash_block('<hr'.self::$empty_element_suffix)."\n",
			$text
		);
	}


	/**
	 * These are all the transformations that occur *within* block-level
	 * tags like paragraphs, headers, and list items.
	 */
	private static $span_gamut = array(
		// Process character escapes, code spans, and inline HTML
		// in one shot.
		'parse_span'             => -30,

		// Process anchor and image tags. Images must come first,
		// because ![foo][f] looks like an anchor.
		'do_images'              =>  10,
		'do_anchors'             =>  20,

		// Make links out of things like `<http://example.com/>`
		// Must come after do_anchors, because you can use < and >
		// delimiters in inline links like [this](<url>).
		'do_auto_links'          =>  30,
		'encode_amps_and_angles' =>  40,

		'do_italics_and_bold'    =>  50,

// This is done in the paragraph section now
//		'do_hard_breaks'         =>  60,
	);

	/*
	 * Run span gamut tranformations.
	 */
	private static function run_span_gamut($text)
	{
		foreach (self::$span_gamut as $method => $priority)
		{
			$text = self::$method($text);
		}

		return $text;
	}


	private static function do_hard_breaks($text)
	{
		// Do hard breaks:
//		return preg_replace_callback('/ {2,}\n/', array(__CLASS__, '_do_hard_breaks_callback'), $text);
		return preg_replace_callback('/\n/', array(__CLASS__, '_do_hard_breaks_callback'), $text);
	}
	private static function _do_hard_breaks_callback($matches) {
		return self::hash_part('<br'.self::$empty_element_suffix."\n");
	}


	/**
	 * Turn Markdown link shortcuts into XHTML <a> tags.
	 */
	private static function do_anchors($text)
	{
		if (self::$in_anchor) return $text;
		self::$in_anchor = TRUE;

		// First, handle reference-style links: [link text] [id]
		$text = preg_replace_callback('{
			(                                 # wrap whole match in $1
			  \[
				('.self::$nested_brackets_re.') # link text = $2
			  \]

			  [ ]?                            # one optional space
			  (?:\n[ ]*)?                     # one optional newline followed by spaces

			  \[
				(.*?)                           # id = $3
			  \]
			)
			}xs',
			array(__CLASS__, '_do_anchors_reference_callback'), $text);

		// Next, inline-style links: [link text](url "optional title")
		$text = preg_replace_callback('{
			(                                          # wrap whole match in $1
			  \[
				('.self::$nested_brackets_re.')          # link text = $2
			  \]
			  \(                                       # literal paren
				[ \n]*
				(?:
					<(.+?)>                                # href = $3
				|
					('.self::$nested_url_parenthesis_re.') # href = $4
				)
				[ \n]*
				(                                        # $5
				  ([\'"])                                # quote char = $6
				  (.*?)                                  # Title = $7
				  \6                                     # matching quote
				  [ \n]*                                 # ignore any spaces/tabs between closing quote and )
				)?                                       # title is optional
			  \)
			)
			}xs',
			array(__CLASS__, '_do_anchors_inline_callback'), $text);

		/*
		 * Last, handle reference-style shortcuts: [link text]
		 * These must come last in case you've also got [link text][1]
		 * or [link text](/foo)
		 */
		$text = preg_replace_callback('{
			(                # wrap whole match in $1
			  \[
				([^\[\]]+)     # link text = $2; cant contain [ or ]
			  \]
			)
			}xs',
			array(__CLASS__, '_do_anchors_reference_callback'), $text);

		self::$in_anchor = FALSE;
		return $text;
	}
	private static function _do_anchors_reference_callback($matches)
	{
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == '')
		{
			// for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}

		// lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset(self::$urls[$link_id]))
		{
			$url = self::$urls[$link_id];
			$url = self::encode_attribute($url);

			$result = '<a href="'.$url.'"';
			if (isset(self::$titles[$link_id]))
			{
				$title   = self::$titles[$link_id];
				$title   = self::encode_attribute($title);
				$result .= ' title="'.$title.'"';
			}

			$link_text = self::run_span_gamut($link_text);
			$result   .= '>'.$link_text.'</a>';
			$result    = self::hash_part($result);
		}
		else
		{
			$result = $whole_match;
		}
		return $result;
	}
	private static function _do_anchors_inline_callback($matches)
	{
		$whole_match	=  $matches[1];
		$link_text		=  self::run_span_gamut($matches[2]);
		$url			    =  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			  =& $matches[7];

		$url = self::encode_attribute($url);

		$result = '<a href="'.$url.'"';
		if (isset($title))
		{
			$title   = self::encode_attribute($title);
			$result .= ' title="'.$title.'"';
		}

		$link_text = self::run_span_gamut($link_text);
		$result   .= '>'.$link_text.'</a>';

		return self::hash_part($result);
	}


	/*
	 * Turn Markdown image shortcuts into <img> tags.
	 */
	private static function do_images($text)
	{

		// First, handle reference-style labeled images: ![alt text][id]
		$text = preg_replace_callback('{
			(                                 # wrap whole match in $1
			  !\[
				('.self::$nested_brackets_re.') # alt text = $2
			  \]

			  [ ]?                            # one optional space
			  (?:\n[ ]*)?                     # one optional newline followed by spaces

			  \[
				(.*?)                           # id = $3
			  \]

			)
			}xs',
			array(__CLASS__, '_do_images_reference_callback'), $text);

		// Next, handle inline images:  ![alt text](url "optional title")
		// Don't forget: encode * and _
		$text = preg_replace_callback('{
			(                                          # wrap whole match in $1
			  !\[
				('.self::$nested_brackets_re.')          # alt text = $2
			  \]
			  \s?                                      # One optional whitespace character
			  \(                                       # literal paren
				[ \n]*
				(?:
					<(\S*)>                                # src url = $3
				|
					('.self::$nested_url_parenthesis_re.') # src url = $4
				)
				[ \n]*
				(                                        # $5
				  ([\'"])                                # quote char = $6
				  (.*?)                                  # title = $7
				  \6                                     # matching quote
				  [ \n]*
				)?                                       # title is optional
			  \)
			)
			}xs',
			array(__CLASS__, '_do_images_inline_callback'), $text);

		return $text;
	}
	private static function _do_images_reference_callback($matches)
	{
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == '')
		{
			$link_id = strtolower($alt_text); // for shortcut links like ![this][].
		}

		$alt_text = self::encode_attribute($alt_text);
		if (isset(self::$urls[$link_id]))
		{
			$url    = self::encode_attribute(self::$urls[$link_id]);
			$result = '<img src="'.$url.'" alt="'.$alt_text.'"';
			if (isset(self::$titles[$link_id]))
			{
				$title   = self::encode_attribute(self::$titles[$link_id]);
				$result .= ' title="'.$title.'"';
			}
			$result .= self::$empty_element_suffix;
			$result  = self::hash_part($result);
		}
		else
		{
			// If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}
	private static function _do_images_inline_callback($matches)
	{
		$whole_match	= $matches[1];
		$alt_text     = $matches[2];
		$url          = $matches[3] == '' ? $matches[4] : $matches[3];
		$title        =& $matches[7];

		$alt_text     = self::encode_attribute($alt_text);
		$url          = self::encode_attribute($url);
		$result       = '<img src="'.$url.'" alt="'.$alt_text.'"';
		if (isset($title))
		{
			$title      = self::encode_attribute($title);
			$result    .= ' title="'.$title.'"'; // $title already quoted
		}
		$result      .= self::$empty_element_suffix;

		return self::hash_part($result);
	}


	private static function do_headers($text)
	{
		// Setext-style headers:
		//	  Header 1
		//	  ========
		//
		//	  Header 2
		//	  --------
		//
		$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx', array(__CLASS__, '_do_headers_callback_setext'), $text);

		// atx-style headers:
		//	# Header 1
		//	## Header 2
		//	## Header 2 with closing hashes ##
		//	...
		//	###### Header 6
		//
		$text = preg_replace_callback('{
				^(\#{1,6}) # $1 = string of #\'s
				[ ]*
				(.+?)      # $2 = Header text
				[ ]*
				\#*        # optional closing #\'s (not counted)
				\n+
			}xm',
			array(__CLASS__, '_do_headers_callback_atx'), $text);

		return $text;
	}
	private static function _do_headers_callback_setext($matches)
	{
		// Terrible hack to check we haven't found an empty list item.
		if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
			return $matches[0];

		$level = $matches[2]{0} == '=' ? 1 : 2;
		$block = '<h'.$level.'>'.self::run_span_gamut($matches[1]).'</h'.$level.'>';
		return "\n".self::hash_block($block)."\n\n";
	}
	private static function _do_headers_callback_atx($matches)
	{
		$level = strlen($matches[1]);
		$block = '<h'.$level.'>'.self::run_span_gamut($matches[2]).'</h'.$level.'>';
		return "\n".self::hash_block($block)."\n\n";
	}


	/*
	 * Form HTML ordered (numbered) and unordered (bulleted) lists.
	 */
	private static function do_lists($text)
	{
		$less_than_tab = self::$tab_width - 1;

		// Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$markers_relist = array(
			$marker_ul_re => $marker_ol_re,
			$marker_ol_re => $marker_ul_re,
		);

		foreach ($markers_relist as $marker_re => $other_marker_re)
		{
			// Re-usable pattern to match any entirel ul or ol list:
			$whole_list_re = '
				(                              # $1 = whole list
				  (                            # $2
					([ ]{0,'.$less_than_tab.'})  # $3 = number of spaces
					('.$marker_re.')             # $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (                            # $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!                        # Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=                        # Lookahead for another kind of list
					    \n
						\3                         # Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; // mx

			// We use a different prefix before nested lists than top-level lists.
			// See extended comment in _ProcessListItems().

			if (self::$list_level)
			{
				$text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx',
					array(__CLASS__, '_do_lists_callback'), $text);
			}
			else
			{
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx',
					array(__CLASS__, '_do_lists_callback'), $text);
			}
		}

		return $text;
	}
	private static function _do_lists_callback($matches)
	{
		// Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$list          = $matches[1];
		$list_type     = preg_match("/$marker_ul_re/", $matches[4]) ? 'ul' : 'ol';

		$marker_any_re = ( $list_type == 'ul' ? $marker_ul_re : $marker_ol_re );

		$list         .= "\n";
		$result        = self::process_list_items($list, $marker_any_re);

		$result        = self::hash_block('<'.$list_type.'>'."\n".$result.'</'.$list_type.'>');
		return "\n".$result."\n\n";
	}

	private static $list_level = 0;

	/**
	 *	Process the contents of a single ordered or unordered list, splitting it
	 *	into individual list items.
	 */
	private static function process_list_items($list_str, $marker_any_re)
	{
		/*
		 * The self::$list_level global keeps track of when we're inside a list.
		 * Each time we enter a list, we increment it; when we leave a list,
		 * we decrement. If it's zero, we're not in a list anymore.
		 *
		 * We do this because when we're not inside a list, we want to treat
		 * something like this:
		 *
		 *		I recommend upgrading to version
		 *		8. Oops, now this line is treated
		 *		as a sub-list.
		 *
		 * As a single paragraph, despite the fact that the second line starts
		 * with a digit-period-space sequence.
		 *
		 * Whereas when we're inside a list (or sub-list), that line will be
		 * treated as the start of a sub-list. What a kludge, huh? This is
		 * an aspect of Markdown's syntax that's hard to parse perfectly
		 * without resorting to mind-reading. Perhaps the solution is to
		 * change the syntax rules such that sub-lists must start with a
		 * starting cardinal number; e.g. "1." or "a.".
		 */

		self::$list_level++;

		// trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?                  # leading line = $1
			(^[ ]*)                # leading whitespace = $2
			('.$marker_any_re.'    # list marker and space = $3
				(?:[ ]+|(?=\n))      # space only required if item is not empty
			)
			((?s:.*?))             # list item text   = $4
			(?:(\n+(?=\n))|\n)     # tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm',
			array(__CLASS__, '_process_list_items_callback'), $list_str);

		self::$list_level--;
		return $list_str;
	}
	private static function _process_list_items_callback($matches)
	{
		$item               =  $matches[4];
		$leading_line       =& $matches[1];
		$leading_space      =& $matches[2];
		$marker_space       =  $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || preg_match('/\n{2,}/', $item))
		{
			// Replace marker with the appropriate whitespace indentation
			$item = $leading_space.str_repeat(' ', strlen($marker_space)).$item;
			$item = self::run_block_gamut(self::outdent($item)."\n");
		}
		else
		{
			// Recursion for sub-lists:
			$item = self::do_lists(self::outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = self::run_span_gamut($item);
		}

		return '<li>'.$item.'</li>'."\n";
	}


	/**
	 * Process Markdown `<pre><code>` blocks.
	 */
	private static function do_code_blocks($text)
	{
		$text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(                                        # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.self::$tab_width.'}              # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.self::$tab_width.'}\S)|\Z)  # Lookahead for non-space at line-start, or end of doc
			}xm',
			array(__CLASS__, '_do_code_blocks_callback'), $text
		);

		return $text;
	}
	private static function _do_code_blocks_callback($matches)
	{
		$codeblock = $matches[1];

		$codeblock = self::outdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		// Trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = '<code><pre>'.$codeblock."\n".'</pre></code>';
		return "\n\n".self::hash_block($codeblock)."\n\n";
	}


	/*
	 * Create a code span markup for $code. Called from handle_span_token.
	 */
	private static function make_code_span($code)
	{
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
		return self::hash_part('<code>'.$code.'</code>');
	}


	private static $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![.,:;]\s)',
		'*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
		'_' => '(?<=\S|^)(?<!_)_(?!_)',
	);
	private static $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![.,:;]\s)',
		'**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
		'__' => '(?<=\S|^)(?<!_)__(?!_)',
	);
	private static $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![.,:;]\s)',
		'***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
		'___' => '(?<=\S|^)(?<!_)___(?!_)',
	);
	private static $em_strong_prepared_relist;

	/**
	 * Prepare regular expressions for searching emphasis tokens in any
	 * context.
	 */
	private static function prepare_italics_and_bold()
	{
		foreach (self::$em_relist as $em => $em_re)
		{
			foreach (self::$strong_relist as $strong => $strong_re)
			{
				// Construct list of allowed token expressions.
				$token_relist = array();
				if (isset(self::$em_strong_relist[$em.$strong]))
				{
					$token_relist[] = self::$em_strong_relist[$em.$strong];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;

				// Construct master expression from list.
				$token_re = '{('.implode('|', $token_relist).')}';
				self::$em_strong_prepared_relist[$em.$strong] = $token_re;
			}
		}
	}

	private static function do_italics_and_bold($text)
	{
		$token_stack  = array('');
		$text_stack   = array('');
		$em           = '';
		$strong       = '';
		$tree_char_em = FALSE;

		while (1)
		{
			/**
			 * Get prepared regular expression for seraching emphasis tokens
			 * in current context.
			 */
			$token_re = self::$em_strong_prepared_relist[$em.$strong];

			/**
			 * Each loop iteration search for the next emphasis token.
			 * Each token is then passed to handle_span_token.
			 */
			$parts          =  preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .=  $parts[0];
			$token          =& $parts[1];
			$text           =& $parts[2];

			if (empty($token))
			{
				// Reached end of text span: empty stack without emitting.
				// any more emphasis.
				while ($token_stack[0])
				{
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}

			$token_len = strlen($token);
			if ($tree_char_em)
			{
				// Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3)
				{
					// Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span           = array_shift($text_stack);
					$span           = self::run_span_gamut($span);
					$span           = '<strong><em>'.$span.'</em></strong>';
					$text_stack[0] .= self::hash_part($span);
					$em             = '';
					$strong         = '';
				}
				else
				{
					// Other closing marker: close one em or strong and
					// change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag            = $token_len == 2 ? "strong" : "em";
					$span           = $text_stack[0];
					$span           = self::run_span_gamut($span);
					$span           = '<'.$tag.'>'.$span.'</'.$tag.'>';
					$text_stack[0]  = self::hash_part($span);
					$$tag           = ''; // $$tag stands for $em or $strong
				}
				$tree_char_em     = FALSE;
			}
			elseif ($token_len == 3)
			{
				if ($em)
				{
					// Reached closing marker for both em and strong.
					// Closing strong marker:
					for ($i = 0; $i < 2; ++$i)
					{
						$shifted_token  = array_shift($token_stack);
						$tag            = strlen($shifted_token) == 2 ? 'strong' : 'em';
						$span           = array_shift($text_stack);
						$span           = self::run_span_gamut($span);
						$span           = '<'.$tag.'>'.$span.'</'.$tag.'>';
						$text_stack[0] .= self::hash_part($span);
						$$tag           = ''; // $$tag stands for $em or $strong
					}
				}
				else
				{
					// Reached opening three-char emphasis marker. Push on token
					// stack; will be handled by the special condition above.
					$em     = $token{0};
					$strong = $em.$em;
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = TRUE;
				}
			}
			elseif ($token_len == 2)
			{
				if ($strong)
				{
					// Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1)
					{
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
					// Closing strong marker:
					array_shift($token_stack);
					$span           = array_shift($text_stack);
					$span           = self::run_span_gamut($span);
					$span           = '<strong>'.$span.'</strong>';
					$text_stack[0] .= self::hash_part($span);
					$strong         = '';
				}
				else
				{
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			}
			else
			{
				// Here $token_len == 1
				if ($em)
				{
					if (strlen($token_stack[0]) == 1)
					{
						// Closing emphasis marker:
						array_shift($token_stack);
						$span           = array_shift($text_stack);
						$span           = self::run_span_gamut($span);
						$span           = '<em>'.$span.'</em>';
						$text_stack[0] .= self::hash_part($span);
						$em             = '';
					}
					else
					{
						$text_stack[0] .= $token;
					}
				}
				else
				{
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}


	private static function do_block_quotes($text)
	{
		$text = preg_replace_callback('/
			  (								    # Wrap whole match in $1
					(?>
						^[ ]*>[ ]?			# ">" at the start of a line
						.+\n					  # rest of the first line
						(.+\n)*					# subsequent consecutive lines
						\n*						  # blanks
					)+
			  )
			/xm',
			array(__CLASS__, '_do_block_quotes_callback'), $text
		);

		return $text;
	}
	private static function _do_block_quotes_callback($matches) {
		$bq = $matches[1];
		// Trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = self::run_block_gamut($bq); // Recurse

		$bq = preg_replace('/^/m', '  ', $bq);
		// These leading spaces cause problem with <pre> content,
		// so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(__CLASS__, '_do_block_quotes_callback2'), $bq);

		return "\n".self::hash_block('<blockquote>'."\n".$bq."\n".'</blockquote>')."\n\n";
	}
	private static function _do_block_quotes_callback2($matches)
	{
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}


	/*
	 * Params:
	 * $text - string to process with html <p> tags
	 */
	private static function form_paragraphs($text)
	{
		// Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		// Wrap <p> tags and unhashify HTML blocks
		foreach ($grafs as $key => $value)
		{
			// Make <br /> of all single line breaks
			$value = self::do_hard_breaks($value);

			if (!preg_match('/^B\x1A[0-9]+B$/', $value))
			{
				// Is a paragraph.
				$value  = self::run_span_gamut($value);
				$value  = preg_replace('/^([ ]*)/', '<p>', $value);
				$value .= '</p>';
				$grafs[$key] = self::unhash($value);
			}
			else
			{
				// Is a block.
				// Modify elements of @grafs in-place...
				$graf        = $value;
				$block       = self::$html_hashes[$graf];
				$graf        = $block;
				$grafs[$key] = $graf;
			}
		}

		return implode("\n\n", $grafs);
	}


	/*
	 * Encode text for a double-quoted HTML attribute. This function
	 * is *not* suitable for attributes enclosed in single quotes.
	 */
	private static function encode_attribute($text)
	{
		$text = self::encode_amps_and_angles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}


	/*
	 * Smart processing for ampersands and angle brackets that need to
	 * be encoded. Valid character entities are left alone unless the
	 * no-entities mode is set.
	 */
	private static function encode_amps_and_angles($text)
	{
		if (self::$no_entities)
		{
			$text = str_replace('&', '&amp;', $text);
		}
		else
		{
			// Ampersand-encoding based entirely on Nat Irons's Amputator
			// MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);;
		}
		// Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}


	private static function do_auto_links($text)
	{
		$text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', array(__CLASS__, '_do_auto_links_url_callback'), $text);

		// Email addresses: <address@domain.foo>
		$text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array(__CLASS__, '_do_auto_links_email_callback'), $text);

		return $text;
	}
	private static function _do_auto_links_url_callback($matches)
	{
		$url  = self::encode_attribute($matches[1]);
		$link = '<a href="'.$url.'">'.$url.'</a>';
		return self::hash_part($link);
	}
	private static function _do_auto_links_email_callback($matches)
	{
		$address = $matches[1];
		$link    = self::encode_email_address($address);
		return self::hash_part($link);
	}


	/*
	 * Input: an email address, e.g. "foo@example.com"
	 *
	 * Output: the email address as a mailto link, with each character
	 *   of the address encoded as either a decimal or hex entity, in
	 *   the hopes of foiling most address harvesting spam bots. E.g.:
	 *
	 *    <p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
	 *        &#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
	 *        &#x6d;">&#x66;o&#111;&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;
	 *        &#101;&#46;&#x63;&#111;&#x6d;</a></p>
	 *
	 * Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
	 * With some optimizations by Milian Wolff.
	 */
	private static function encode_email_address($addr)
	{
		$addr  = 'mailto:'.$addr;
		$chars = preg_split('/(?<!^)(?!$)/', $addr);
		$seed  = (int)abs(crc32($addr) / strlen($addr)); // Deterministic seed.

		foreach ($chars as $key => $char)
		{
			$ord = ord($char);
			// Ignore non-ascii chars.
			if ($ord < 128)
			{
				$r = ($seed * (1 + $key)) % 100; // Pseudo-random function.
				// roughly 10% raw, 45% hex, 45% dec
				// '@' *must* be encoded. I insist.
				if ($r > 90 && $char != '@'); // do nothing
				elseif ($r < 45)  $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}

		$addr = implode('', $chars);
		$text = implode('', array_slice($chars, 7)); // text without `mailto:`
		$addr = '<a href="'.$addr.'">'.$text.'</a>';

		return $addr;
	}


	/*
	 * Take the string $str and parse it into tokens, hashing embeded HTML,
	 * escaped characters and handling code spans.
	 */
	private static function parse_span($str)
	{
		$output = '';

		$span_re = '{
				(
					\\\\'.self::$escape_chars_re.'
				|
					(?<![`\\\\])
					`+                           # code span marker
			'.( self::$no_markup ? '' : '
				|
					<!--    .*?     -->          # comment
				|
					<\?.*?\?> | <%.*?%>          # processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:_]+       # regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs';

		while (1)
		{
			//
			// Each loop iteration seach for either the next tag, the next
			// openning code span marker, or the next escaped character.
			// Each token is then passed to handle_span_token.
			//
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

			// Create token from text preceding tag.
			if ($parts[0] != '')
			{
				$output .= $parts[0];
			}

			// Check if we reach the end.
			if (isset($parts[1]))
			{
				$output .= self::handle_span_token($parts[1], $parts[2]);
				$str     = $parts[2];
			}
			else
			{
				break;
			}
		}

		return $output;
	}


	/*
	 * Handle $token provided by parse_span by determining its nature and
	 * returning the corresponding value that should replace it.
	 */
	private static function handle_span_token($token, &$str)
	{
		switch ($token{0})
		{
			case '\\':
				return self::hash_part("&#". ord($token{1}). ';');
			case '`':
				// Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm',
					$str, $matches))
				{
					$str      = $matches[2];
					$codespan = self::make_code_span($matches[1]);
					return self::hash_part($codespan);
				}
				return $token; // Return as text since no ending marker found.
			default:
				return self::hash_part($token);
		}
	}


	/*
	 * Remove one level of line-leading tabs or spaces
	 */
	private static function outdent($text)
	{
		return preg_replace('/^(\t|[ ]{1,'.self::$tab_width.'})/m', '', $text);
	}


	// String length function for detab. `_init_detab` will create a function to
	// hanlde UTF-8 if the default function does not exist.
	private static $utf8_strlen = 'mb_strlen';

	/*
	 * Replace tabs with the appropriate amount of space.
	 */
	private static function detab($text)
	{
		// For each line we separate the line in blocks delemited by
		// tab characters. Then we reconstruct every line by adding the
		// appropriate number of space between each blocks.

		$text = preg_replace_callback('/^.*\t.*$/m', array(__CLASS__, '_detab_callback'), $text);

		return $text;
	}
	private static function _detab_callback($matches) {
		$line   = $matches[0];
		$strlen = self::$utf8_strlen; // strlen function for UTF-8.

		// Split in blocks.
		$blocks = explode("\t", $line);
		// Add each blocks to the line.
		$line   = $blocks[0];
		unset($blocks[0]); // Do not add first block twice.
		foreach ($blocks as $block)
		{
			// Calculate amount of space, insert spaces, insert block.
			$amount = self::$tab_width - $strlen($line, 'UTF-8') % self::$tab_width;
			$line  .= str_repeat(' ', $amount).$block;
		}
		return $line;
	}

	/*
	 * Check for the availability of the function in the `utf8_strlen` property
	 * (initially `mb_strlen`). If the function is not available, create a
	 * function that will loosely count the number of UTF-8 characters with a
	 * regular expression.
	 */
	private static function _init_detab() {
		if (function_exists(self::$utf8_strlen)) return;
		self::$utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/",
			$text, $m);');
	}


	/*
	 * Swap back in all the tags hashed by _HashHTMLBlocks.
	 */
	private static function unhash($text)
	{
		return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(__CLASS__, '_unhash_callback'), $text);
	}
	private static function _unhash_callback($matches)
	{
		return self::$html_hashes[$matches[0]];
	}

}
