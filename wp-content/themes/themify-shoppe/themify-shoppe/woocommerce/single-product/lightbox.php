<div id="pagewrap" class="tf_box tf_h">
	<div id="body" class="tf_clear tf_box tf_mw tf_h clearfix">
		<div id="layout" class="pagewidth tf_box clearfix">
			<div id="content" class="tf_h tf_left tf_box clearfix">
				<div class="tf_box product-lightbox">
					<?php if (have_posts()){ 
							the_post();
							wc_get_template_part('content', 'single-product'); 
						} 
					 ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
if(themify_get_gallery_type()==='default'){
	woocommerce_photoswipe();
}