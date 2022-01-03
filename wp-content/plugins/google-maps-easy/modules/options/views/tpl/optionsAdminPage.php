<div class="wrap">
    <div class="supsystic-plugin">
        <section class="supsystic-content">
            <nav class="supsystic-navigation supsystic-sticky <?php dispatcherGmp::doAction('adminMainNavClassAdd')?>">
                <ul class="supsystic-main-navigation-list">
					<?php foreach($this->tabs as $tabKey => $tab) { ?>
						<?php if(isset($tab['hidden']) && $tab['hidden']) continue;?>
						<li class="<?php echo (($this->activeTab == $tabKey || in_array($tabKey, $this->activeParentTabs)) ? 'active' : '')?>" data-tab-key="<?php echo esc_attr($tabKey)?>">
							<a href="<?php echo esc_attr($tab['url'])?>">
								<?php if(isset($tab['fa_icon'])) { ?>
									<i class="fa <?php echo esc_attr($tab['fa_icon'])?>"></i>
								<?php } elseif(isset($tab['wp_icon'])) { ?>
									<i class="dashicons-before <?php echo esc_attr($tab['wp_icon'])?>"></i>
								<?php } elseif(isset($tab['icon'])) { ?>
									<i class="<?php echo esc_attr($tab['icon'])?>"></i>
								<?php }?>
								<?php echo esc_attr($tab['label'])?>
							</a>
						</li>
					<?php }?>
                </ul>
            </nav>
            <div class="supsystic-container supsystic-<?php echo esc_attr($this->activeTab)?>">
				<?php dispatcherGmp::doAction('discountMsg');?>
				<?php echo viewGmp::ksesString($this->content);?>
                <div class="clear"></div>
            </div>
        </section>
    </div>
</div>
<!--Option available in PRO version Wnd-->
<div id="gmpOptInProWnd" style="display: none;" title="<?php _e('Improve Free version', GMP_LANG_CODE)?>">
	<p>
		<?php printf(__("Please be advised that this option is available only in <a target='_blank' href='%s'>PRO version</a>. You can <a target='_blank' href='%s' class='button'>Get PRO</a> today and get this and other PRO option for your Maps!", GMP_LANG_CODE), $this->mainLink, $this->mainLink)?>
	</p>
</div>
