<?php

/**
 * Handles VK AdNetwork Inline CSS settings.
 */
class VK_Adnetwork_Inline_Css {
    /**
     *  Holds the state if inline css should be output or not.
     *
     * @var bool
     */
    protected $add_inline_css;

    /**
     * Initialize the module.
     */
    public function __construct() { }

    /**
     * Adds inline css.
     *
     * @param array     $wrapper       Add wrapper array.
     * @param string    $css           Custom inline css.
     * @param bool|null $global_output Whether this ad is using cache-busting.
     *
     * @return array
     */
    public function add_css( $wrapper, $css, $global_output ) {
        $this->add_inline_css = $this->add_inline_css && $global_output !== false;
        if ( ! $this->add_inline_css ) {
            return $wrapper;
        }

        $styles               = $this->get_styles_by_string( $css );
        $wrapper['style']     = empty( $wrapper['style'] ) ? $styles : array_merge( $wrapper['style'], $styles );
        $this->add_inline_css = false;

        return $wrapper;
    }

    /**
     * Reformat css styles string to array.
     *
     * @param string $string CSS-Style.
     *
     * @return array
     */
    private function get_styles_by_string( $string ) {
        $chunks = array_chunk( preg_split( '/[:;]/', $string ), 2 );
        array_walk_recursive( $chunks, function( &$value ) {
            $value = trim( $value );
        } );

        return array_combine( array_filter( array_column( $chunks, 0 ) ), array_filter( array_column( $chunks, 1 ) ) );
    }

}
