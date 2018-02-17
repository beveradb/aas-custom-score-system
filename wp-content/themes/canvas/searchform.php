<?php global $woo_options; ?>
<div class="search_main">
    <form method="get" class="searchform" action="<?php echo home_url( '/' ); ?>" >
        <input type="text" class="field s" name="s" value="<?php _e( 'Search...', 'woothemes' ); ?>" onfocus="if (this.value == '<?php _e( 'Search...', 'woothemes' ); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search...', 'woothemes' ); ?>';}" />
        <button type="submit" class="fa fa-search submit" name="submit" value="<?php _e( 'Search', 'woothemes' ); ?>"></button>
    </form>
    <div class="fix"></div>
</div>