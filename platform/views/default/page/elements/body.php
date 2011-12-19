<body>
	<div id="mainShell">
		<div id="contentShell">
			<header><?php echo view('page/elements/pagetop', $vars); ?></header>
			<section>
				<div id="pageContent">
					<div class="content <?php 
						if (page_get_context())
							echo " ".page_get_context();
					?>">
						<?php echo $vars['body']; ?>
					</div>
				</div>
			</section>
			<footer><?php echo view('page/elements/pagebottom', $vars); ?></footer>
		</div>
	</div>
</body>