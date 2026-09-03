<?php
/**
 * Regression tests for the CSS and HTML minifiers.
 *
 * Run with:  php tests/test-minifiers.php    (from the theme root, no WordPress)
 *
 * These exist because a minifier that silently returns *slightly* wrong output
 * is the worst kind of bug in this codebase: the Customizer preview skips
 * minification entirely, so the damage is invisible where the site is edited
 * and only appears to visitors. Two real failures are pinned here:
 *
 *   1. An apostrophe inside a CSS comment ("the firm's world") opened a string
 *      literal that ran to the next apostrophe 15 KB later, swallowing the
 *      whole :root token block. Every page rendered with no custom properties.
 *   2. Squeezing ":" wherever brace depth was above zero turned the selector
 *      ".statement :where(h2)" into ".statement:where(h2)" — a descendant
 *      combinator becoming a compound selector, which matches nothing.
 *
 * Every case below is a thing that actually broke, or a thing one character
 * away from breaking.
 *
 * @package Brooks_Law
 */

$theme = dirname( __DIR__ );

// Pull the two functions out of the shipped file so the tests exercise the
// real code rather than a copy that can drift away from it.
$source = file_get_contents( $theme . '/inc/performance.php' );
foreach ( array( 'brooks_law_minify_css', 'brooks_law_minify_html' ) as $fn ) {
	preg_match( '/function ' . $fn . '\( \$\w+ \) \{.*?\n\}\n/s', $source, $m );
	eval( $m[0] );
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( $length = 12, $special = true, $extra = false ) {
		return substr( bin2hex( random_bytes( 16 ) ), 0, $length );
	}
}

$pass = 0;
$fail = 0;

/**
 * Assert that minified CSS contains a needle.
 */
function css_has( $label, $input, $needle ) {
	global $pass, $fail;
	$got = brooks_law_minify_css( $input );
	if ( false !== strpos( $got, $needle ) ) {
		$pass++;
		printf( "  ok    %s\n", $label );
	} else {
		$fail++;
		printf( "  FAIL  %s\n        want substring: %s\n        got: %s\n", $label, $needle, $got );
	}
}

/**
 * Assert that minified CSS does NOT contain a needle.
 */
function css_lacks( $label, $input, $needle ) {
	global $pass, $fail;
	$got = brooks_law_minify_css( $input );
	if ( false === strpos( $got, $needle ) ) {
		$pass++;
		printf( "  ok    %s\n", $label );
	} else {
		$fail++;
		printf( "  FAIL  %s\n        must NOT contain: %s\n        got: %s\n", $label, $needle, $got );
	}
}

/**
 * Assert exact HTML minifier output.
 */
function html_is( $label, $input, $want ) {
	global $pass, $fail;
	$got = brooks_law_minify_html( $input );
	if ( $got === $want ) {
		$pass++;
		printf( "  ok    %s\n", $label );
	} else {
		$fail++;
		printf( "  FAIL  %s\n        want: %s\n        got:  %s\n", $label, var_export( $want, true ), var_export( $got, true ) );
	}
}

echo "CSS minifier\n";

css_has(
	'apostrophe in a comment does not swallow the rules after it',
	"/* the firm's world */\n:root { --court: #12202e; }\n/* another firm's note */\n.a { color: red; }",
	':root{--court:#12202e}'
);
css_has( 'and the rule after the second apostrophe survives too', "/* firm's */\n:root{--a:1}\n/* firm's */\n.b{color:red}", '.b{color:red}' );

css_has( 'descendant combinator before a pseudo is preserved', '.statement :where(h2, h3) { color: red; }', '.statement :where(h2, h3){color:red}' );
css_has( 'compound selector stays compound', '.statement:hover { color: red; }', '.statement:hover{color:red}' );
css_has( 'descendant combinator survives inside nested at-rules', "@supports (container-type: inline-size) { @container c (max-width: 330px) { .a :where(h2) { color: red; } } }", '.a :where(h2){color:red}' );

css_has( 'calc() keeps the spaces its grammar requires', '.a { padding: calc(64px + env(safe-area-inset-bottom, 0px)); }', 'calc(64px + env(safe-area-inset-bottom, 0px))' );
css_has( 'calc() subtraction too', '.a { width: calc(100% - 20px); }', 'calc(100% - 20px)' );

css_has( 'string content is untouched', '.a::after { content: "Note: one, two"; }', 'content:"Note: one, two"' );
css_has( 'escape sequences keep their terminating space', '.a::before { content: "Portrait \\A 1200 \\00D7 800 px"; }', '"Portrait \\A 1200 \\00D7 800 px"' );
css_has( 'trailing space inside a string survives', '.a::before { content: "— "; }', 'content:"— "' );

css_has( 'unquoted url() payload is opaque', ".a { background: url(data:image/svg+xml,%3Csvg viewBox='0 0 2 2'%3E %3C/svg%3E); }", "%3Csvg viewBox='0 0 2 2'%3E %3C/svg%3E" );
css_has( 'quoted url() payload is opaque', '.a { background: url("a b.png"); }', 'url("a b.png")' );

css_has( 'top-level media prelude keeps its spacing', '@media (min-width: 700px) { .a { color: red; } }', '@media (min-width: 700px){' );
css_has( 'nested at-rule prelude keeps its spacing', '@supports (display: grid) { @container c (max-width: 330px) { .a { color: red; } } }', '(max-width: 330px){' );
css_has( 'keyframes survive', '@keyframes k { from { opacity: 0; } to { opacity: 1; } }', '@keyframes k{from{opacity:0}to{opacity:1}}' );

css_has( 'licence comment is preserved', "/*! keep me */\n.a { color: red; }", '/*! keep me */' );
css_lacks( 'ordinary comment is removed', "/* drop me */\n.a { color: red; }", 'drop me' );
css_lacks( 'comment containing a brace cannot unbalance the parser', "/* } */ .a { color: red; }", '/* }' );

css_has( 'relative colour syntax is untouched', ':root { --x: oklch(from var(--y) calc(l - 0.08) c h); }', 'oklch(from var(--y) calc(l - 0.08) c h)' );
css_has( 'var() fallback survives', '.a { color: var(--ink, #222933); }', 'var(--ink, #222933)' );

css_has( 'unterminated string does not lose the file', '.a { content: "oops', '.a{content:"oops' );
css_has( 'unterminated comment is dropped without a crash', ".a { color: red; }\n/* oops", '.a{color:red}' );

echo "\nHTML minifier\n";

html_is( 'a forged placeholder cannot pull in another element', '<div><!--BLFPROTECT0--></div><script>1</script>', '<div></div><script>1</script>' );
html_is( 'attribute whitespace is preserved', '<img alt="a  b" src="x">', '<img alt="a  b" src="x">' );
html_is( 'a ">" inside an attribute does not end the tag', '<img alt="a > b" title="x  y">', '<img alt="a > b" title="x  y">' );
html_is( 'JSON in a data- attribute survives', '<div data-j=\'{"a": 1,  "b": 2}\'>t</div>', '<div data-j=\'{"a": 1,  "b": 2}\'>t</div>' );
html_is( 'textarea content is untouched', "<textarea>\n  keep   me\n</textarea>", "<textarea>\n  keep   me\n</textarea>" );
html_is( 'pre content is untouched', '<pre>a  b</pre>', '<pre>a  b</pre>' );
html_is( 'inline spacing between elements is kept', "<p>a <b>b</b>\n<i>c</i></p>", '<p>a <b>b</b> <i>c</i></p>' );
html_is( 'comments are stripped', '<div><!-- x --><span>k</span></div>', '<div><span>k</span></div>' );
html_is( 'conditional comments are kept', '<!--[if IE]><i>x</i><![endif]-->', '<!--[if IE]><i>x</i><![endif]-->' );

printf( "\n%d passed, %d failed\n", $pass, $fail );
exit( $fail > 0 ? 1 : 0 );
