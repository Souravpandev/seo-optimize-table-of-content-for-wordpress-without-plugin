/* Dynamic, no-database Table-of-Contents  */
function bno_generate_dynamic_toc( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$pattern = '/<h([2-3]).*?>(.*?)<\/h[2-3]>/i';
	if ( ! preg_match_all( $pattern, $content, $m ) ) {
		return $content;   // exit if no headings
	}

	$toc  = '<div class="toc-container" role="navigation" aria-labelledby="toc-title">';
	$toc .= '<div class="toc-title-row" style="display:flex;align-items:center;justify-content:space-between;"><div id="toc-title" class="toc-title-desktop">On this page</div><span id="toc-toggle-btn" class="toc-toggle-btn" aria-expanded="false" aria-controls="toc-content">SHOW <span id="toc-toggle-arrow">▼</span></span></div>';
	$toc .= '<div id="toc-content" class="toc" style="display:none;"><ul>';

	$schema_items = [];
	$open_sub     = false;

	foreach ( $m[0] as $i => $h_markup ) {
		$level = $m[1][ $i ];                 // 2 or 3
		$text  = wp_strip_all_tags( $m[2][ $i ] );
		$id    = sanitize_title( $text );

		if ( $level === '2' ) {
			if ( $open_sub ) {
				$toc .= '</ul></li>';
				$open_sub = false;
			}
			$next = $m[1][ $i + 1 ] ?? null;
			$toc .= '<li class="toc-h2"><a href="#' . $id . '">' . $text . '</a>';
			if ( $next === '3' ) {
				$toc .= '<ul>';
				$open_sub = true;
			}
		} else {
			$toc .= '<li><a href="#' . $id . '">' . $text . '</a></li>';
		}

		$content = str_replace(
			$h_markup,
			'<h' . $level . ' id="' . $id . '">' . $text . '</h' . $level . '>',
			$content
		);

		$schema_items[] = [
			'@type'    => 'ListItem',
			'position' => count( $schema_items ) + 1,
			'name'     => $text,
			'item'     => get_permalink() . '#' . $id,
		];
	}
	if ( $open_sub ) {
		$toc .= '</ul></li>';
	}
	$toc .= '</ul></div></div>';

	$schema = [
		'@context'         => 'https://schema.org',
		'@type'            => 'TableOfContents',
		'itemListElement'  => $schema_items,
	];
	$json = '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';

	return $json . $toc . $content;
}
add_filter( 'the_content', 'bno_generate_dynamic_toc', 20 );

/* Toggle script – keeps original button behaviour, no DB touch */
function bno_toc_toggle_script() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	?>
	<style>
	/* Mobile sticky TOC styles */
	@media (max-width: 768px) {
		.toc-container {
			position: relative;
			z-index: 1000;
		}
		
		.toc-container.sticky {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			background: white;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			z-index: 1000;
		}
		
		.toc-container.sticky .toc-title-row {
			padding: 10px 15px;
		}
		
		.toc-container.sticky #toc-content {
			max-height: 60vh;
			overflow-y: auto;
		}
	}
	</style>
	<script>
	document.addEventListener('DOMContentLoaded',function(){
		const btn=document.getElementById('toc-toggle-btn');
		const toc=document.getElementById('toc-content');
		const tocContainer=document.querySelector('.toc-container');
		if(!btn||!toc||!tocContainer) return;
		
		// Toggle functionality
		btn.addEventListener('click',()=>{
			const hidden=toc.style.display==='none';
			toc.style.display=hidden?'':'none';
			btn.innerHTML=(hidden?'HIDE ▲':'SHOW ▼').replace('▲','<span id="toc-toggle-arrow">▲</span>').replace('▼','<span id="toc-toggle-arrow">▼</span>');
			btn.setAttribute('aria-expanded',hidden);
		});
		
		// Mobile sticky functionality
		if(window.innerWidth <= 768) {
			const tocOffset = tocContainer.offsetTop;
			
			window.addEventListener('scroll', function() {
				if(window.pageYOffset > tocOffset) {
					tocContainer.classList.add('sticky');
				} else {
					tocContainer.classList.remove('sticky');
				}
			});
		}
	});
	</script>
	<?php
}
add_action( 'wp_head', 'bno_toc_toggle_script', 5 );
