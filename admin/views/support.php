<?php
defined( 'ABSPATH' ) || exit;

/**
 * The view for the settings page
 * VK_Adnetwork_Admin_Menu > add_plugin_admin_menu > display_plugin_support_page
 *
 */
?><div class="wrap">
  <h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
  <?php VK_Adnetwork_Checks::show_issues(); ?>

  <?php settings_errors(); ?>

  <div id="vk_adnetwork-overview">
    <div class="postbox position-full">
      <div class="inside">
        <h3><?php
            // Навигация
            esc_html_e( 'Navigation', 'vk-adnetwork' );  ?></h3>
        <ul class="page-navigation">
          <li><a href="#VKAdNetworkplugindesigned"><?php
              // Плагин <strong>VK AdNetwork</strong> предназначен для размещения рекламных блоков Рекламной сети VK на вашем сайте.
              echo wp_kses( __( 'The <strong>VK AdNetwork</strong> plugin is designed to place ad units of the VK Advertising Network on your website.', 'vk-adnetwork' ), [ 'strong' => true ] );
              ?></a>
          </li>
          <li><a href="#recomendations"><?php
              // Рекомендации
              esc_html_e( 'Recommendations', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#start"><?php
              // Начало
              esc_html_e( 'Beginning', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#ads_embedding"><?php
              // Встраивание рекламы на ваш сайт
              esc_html_e( 'Embedding ads on your site', 'vk-adnetwork' );
              ?></a>
          </li>

          <li><a href="#AdBlockParameters"><?php
              // Варианты макета / вывода
              esc_html_e( 'Layout/Output options', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#ad_position_relative_to_text"><?php
              // Положение рекламы относительно текста
              esc_html_e( 'The position of the advertisement relative to the text', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#shortcodes"><?php
              // Шорткоды
              esc_html_e( 'Shortcodes', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#troubleshooting"><?php
              // Если реклама не отображается
              esc_html_e( 'If the advertisement is not displayed', 'vk-adnetwork' );
              ?></a>
          </li>
          <li><a href="#debug_mode"><?php
              // Использование режима отладки рекламы
              esc_html_e( 'Using the ad debugging mode', 'vk-adnetwork' );
              ?></a>
          </li>

        </ul>
      </div>
    </div>
    <div class="postbox position-full">
      <div class="inside">
        <h3 id="VKAdNetworkplugindesigned"><?php
            // Плагин <strong>VK AdNetwork</strong> предназначен для размещения рекламных блоков Рекламной сети VK на вашем сайте.
            echo wp_kses( __( 'The <strong>VK AdNetwork</strong> plugin is designed to place ad units of the VK Advertising Network on your website.', 'vk-adnetwork' ), [ 'strong' => true ] ); ?></h3>
        <p>
          <?php
            // Для работы с плагином и размещения рекламных блоков Вам не нужны дополнительные знания, только нужно сделать несколько действий:
            esc_html_e( 'To work with the plugin and place ad blocks, you do not need additional knowledge, you just need to do a few actions:', 'vk-adnetwork' ); ?>
        </p>
        <ul>
          <li> &nbsp; &nbsp; 1. <?php
            // Зарегистрироваться в Рекламной сети VK  <a href="%s" target="_blank">по ссылке</a>;
            // translators: %s is the address of the registration page of the VK advertising network
            echo wp_kses( sprintf( __( 'Register in the <strong>VK Advertising Network</strong> using <a href="%s" target="_blank">the link</a>;', 'vk-adnetwork' ),
                VK_ADNETWORK_URL . 'partner'),
                [ 'strong' => true, 'a' => ['href' => true, 'target' => true] ] );
            ?>
          </li>
          <li> &nbsp; &nbsp; 2. <?php
            // Получить <strong>client_id</strong> и <strong>client_secret</strong> (<a href="%s" target="_blank">как это сделать</a>);
            echo wp_kses( sprintf( __( 'Get <strong>client_id</strong> and <strong>client_secret</strong> (<a href="%s" target="_blank">how to do it</a>);', 'vk-adnetwork' ),
                VK_ADNETWORK_URL . 'help/articles/partner_management_api'),
                [ 'strong' => true, 'a' => ['href' => true, 'target' => true] ] );
            ?>
          </li>
          <li> &nbsp; &nbsp; 3. <?php
            // Ввести полученые данные на странице <a href="%s">«Настройки»</a>.
            // translators: %s is the address of the page with the settings of this plugin
            echo wp_kses( sprintf( __( 'Enter the received data on the page <a href="%s">"Settings"</a>.', 'vk-adnetwork' ),
                admin_url('admin.php?page=vk-adnetwork-settings')),
                [ 'a' => ['href' => true] ] );
            ?>
          </li>
        </ul>

        <p>
          <?php
            // Плагин создаст:
            esc_html_e( 'The plugin will create:', 'vk-adnetwork' );
          ?>
        </p>
        <ul>
          <li> &nbsp; &nbsp; - <?php
            // площадку для вашего сайта в кабинете <strong>Рекламной сети VK</strong> (ее названием будет <strong>WP: ваш-домен</strong>)
            echo wp_kses( __( 'a platform for your site in the account of the  <strong>VK Advertising network</strong> (its name will be <strong>WP: your-domain</strong>)', 'vk-adnetwork' ), [ 'strong' => true ] );
            ?>
          </li>
          <li> &nbsp; &nbsp; - <?php
            // рекламный блок на всех страницах вашего сайта в Wordpress, расположенный над контентом.
            esc_html_e( 'an ad block on all pages of your Wordpress site, located above the content.', 'vk-adnetwork' );
            ?>
          </li>
        </ul>

        <p>
          <?php
              // После создания рекламный блок будет доступен для редактирования на странице <a href="%s" target="_blank">«Реклама»</a>
              // translators: %s is the address of the page with the settings of this plugin
              echo wp_kses( sprintf( __( 'After creating the ad block, it will be editable on the page <a href="%s" target="_blank">"Advertising"</a>', 'vk-adnetwork' ),
                  admin_url('admin.php?page=vk-adnetwork-settings')),
                  [ 'a' => ['href' => true, 'target' => true] ] );
              // и вы можете поменять позицию рекламного блока.
              esc_html_e( 'and you can change the position of the ad block.', 'vk-adnetwork' );
              // После чего вы можете опубликовать рекламный блок (начать трансляцию рекламы).
              esc_html_e( 'After that, you can publish the ad block (start broadcasting the ad).', 'vk-adnetwork' );
          ?>
        </p>

        <p><?php
            // После регистрации в <strong>Рекламной сети VK</strong>  и указания всех необходимых данных мы проверим информацию в течение 24 часов.
            echo wp_kses( __( 'After registering in the <strong>VK Advertising Network</strong> and providing all the necessary data, we will check the information within 24 hours.', 'vk-adnetwork' ),
                [ 'strong' => true ] );
            // Со статусом проверки Вы можете ознакомиться как в личном кабинете <strong>Рекламной сети VK</strong>, так и на странице <a href="%s" target="_blank">«Статистика»</a>.
            // translators: %s is the address of the page with statistics and graphs of this plugin
            echo wp_kses( sprintf( __( 'You can get acquainted with the verification status both in the personal account of the <strong>VK Advertising Network</strong> and on the <a href="%s" target="_blank">"Statistics"</a> page.', 'vk-adnetwork' ),
                admin_url('admin.php?page=vk-adnetwork-settings')),
                [ 'strong' => true, 'a' => ['href' => true, 'target' => true] ] );
            ?>
        </p>

        <figure>
              <?php
              // Страница «Реклама» плагина, после создания первого рекламного блока
              echo wp_kses('<img src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-first-block.png')
                  . '" title="' . __( 'The "Advertising" page of the plugin, after creating the first ad block', 'vk-adnetwork' ) . '">',
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
        </figure>


          <h3 id="recomendations"><?php
            // Рекомендации
            esc_html_e( 'Recommendations', 'vk-adnetwork' );
          ?>
        </h3>

        <p><?php
            // Если вы хотите показывать рекламу в разных местах размещения, рекомендуем завести новый рекламный блок.
            esc_html_e( 'If you want to show ads in different placements, we recommend starting a new ad unit.', 'vk-adnetwork' );
            // Это позволит не только увеличить доход, но и получать статистику по каждому блоку отдельно.
            esc_html_e( 'This will allow not only to increase income, but also to receive statistics for each block separately.', 'vk-adnetwork' );
          ?>
        </p>

        <p><?php
          // Остались вопросы? <a href="%s" target="_blank">Спросите у нас</a>.
          // translators: %s is the address of the support on the VK advertising network
          echo wp_kses( sprintf( __( 'Any other questions? <a href="mailto:%s" target="_blank">Ask us</a>.', 'vk-adnetwork' ),
              'adnetwork_support@vk.company'),
              [ 'a' => ['href' => true, 'target' => true] ] );
          ?>
        </p>
      </div>
    </div>

    <div id="ad-usage-box" class="postbox position-full">
      <div class="inside">
        <h3 id="start"><?php
            // Начало (недопереведено т.к. времянка)
            esc_html_e( 'Beginning', 'vk-adnetwork' );
            ?>
        </h3>

        <p><?php
            // Рекламные блоки — это контент, который вы хотите отобразить, например баннер <strong>Рекламной сети VK</strong>.
            echo wp_kses( __( 'Ad blocks are the content that you want to display, for example, the banner of the <strong>VK Advertising Network</strong>.', 'vk-adnetwork' ),
                            [ 'strong' => true ] );
            ?>
        </p>
        <p><?php
            // Чтобы создать рекламный блок, перейдите в раздел <b>«VK AdNetwork» > «Реклама»</b> и нажмите <b>«Новый рекламный блок»</b> - откроется окно редактирования.
            echo wp_kses( __( 'To create an ad block, go to <b>"VK AdNetwork" > "Advertising"</b> and click <b>"New ad block"</b> - an editing window will open.', 'vk-adnetwork' ),
                [ 'b' => true ] );
            ?>
        </p>
        <p><?php
            // Начните с определения заголовка для вашего рекламного блока.
            esc_html_e( 'Start by defining the title for your ad block.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // Мы рекомендуем использовать описательные заголовки, которые помогут вам позже идентифицировать объявление, например, "Кампания А | Таблица лидеров | Заголовок".
            esc_html_e( 'We recommend using descriptive headings that will help you identify the ad later, for example, "Campaign A | Leaderboard | Headline".', 'vk-adnetwork' );
            ?>
        </p>

        <h3 id="ads_embedding"><?php
            // Встраивание рекламы на ваш сайт
            esc_html_e( 'Embedding ads on your site', 'vk-adnetwork' );
          ?>
        </h3>

        <p><?php
            // <b>«VK AdNetwork»</b> предлагает различные способы включения рекламных блоков на ваш веб-сайт.
            echo wp_kses( __( '<b>VK AdNetwork</b> offers various ways to include ad blocks on your website.', 'vk-adnetwork' ), [ 'b' => true ] ); ?></p>
        <p><?php
            // Вы можете использовать один из следующих методов:
            esc_html_e( 'You can use one of the following methods:', 'vk-adnetwork' ); ?></p>
        <ol>
          <li><?php
              // Через рекламные позиции.
              esc_html_e( 'Through advertising positions.', 'vk-adnetwork' );
              ?>
          </li>
          <li><?php
              // Вставка <a href="#shortcodes">шорткодов</a> вручную.
              echo wp_kses( __( 'Inserting <a href="#shortcodes">shortcodes</a> manually.', 'vk-adnetwork' ),
                  [ 'a' => ['href' => true] ] );
              ?>
          </li>
        </ol>

        <figure>
              <?php
              // Встраивание рекламы на ваш сайт
              echo wp_kses('<img src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-position-top-content-bottom-footer-widget-shortcode.png')
                  . '" title="' . __( 'Embedding ads on your site', 'vk-adnetwork' ) . '">',
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
        </figure>

     </div>
    </div>

    <div id="ad-output-box" class="postbox position-full">
      <div class="inside">
        <h3 id="AdBlockParameters"><?php // -x- Варианты макета / вывода \\ Layout/Output options
            // Параметры рекламного блока
            esc_html_e( 'Ad Block Parameters', 'vk-adnetwork' );
            ?>
        </h3>

        <p><?php
            // Чтобы изменить настройки вывода блока, перейдите в раздел <a href="%s" target="_blank">«Реклама»</a> и создайте новый рекламный блок или откройте существующий.
            echo wp_kses( sprintf( __( 'To change the block output settings, go to <a href="%s" target="_blank">"Advertising"</a> and create a new ad block or open an existing one.', 'vk-adnetwork' ),
                admin_url('edit.php?post_type=vk_adnetwork')),
                [ 'a' => ['href' => true, 'target' => true] ] );
            ?>
        </p>
        <p><?php
            // В разделе «Параметры рекламного блока» вы сможете выбрать варианты отображения рекламного блока.
            esc_html_e( 'In the section "Ad Block Parameters" you can select the display options for the ad block.', 'vk-adnetwork' );
            ?>
        </p>
        <figure>
          <?php
            // Параметры рекламного блока
            echo wp_kses('<img src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-parameters-inpage-footer-widget.png')
                . '" title="' . __( 'Ad Block Parameters', 'vk-adnetwork' ) . '">',
                ['img' => ['src' => true, 'title' => true]]);
          ?>
        </figure>

        <h3 id="ad_position_relative_to_text"><?php // Положение рекламы относительно текста
            esc_html_e( 'The position of the advertisement relative to the text', 'vk-adnetwork' );
            ?>
        </h3>

          <p><?php
              // Для рекламного блока формата «InPage» вы можете выбрать три варианта расположения рекламы в разделе «Выравнивание блока по вертикали»:
              esc_html_e( 'For an "InPage" format ad block, you can select three options for placing ads in the ”Vertical alignment of the block”:', 'vk-adnetwork' );
              ?>
          </p>
          <ol>
              <li><?php
                  // В верхней секции
                  esc_html_e( 'In the upper section', 'vk-adnetwork' );
                ?>
              </li>
              <li><?php
                  // В контенте страницы
                  esc_html_e( 'In the content of the page', 'vk-adnetwork' );
                ?>
              </li>
              <li><?php
                  // В нижней секции
                  esc_html_e( 'In the lower section', 'vk-adnetwork' );
                ?>
              </li>
          </ol>

          <figure>
              <?php
              // Сверху контента - В середине контента (после какого-то абзаца) - После контента
              echo wp_kses( sprintf('<img src="%s" title="%s - %s - %s">',
                  esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-content-before-within-after.png'),
                  __( 'Top of the content', 'vk-adnetwork' ),
                  __( 'In the middle of the content (after some paragraph)', 'vk-adnetwork' ),
                  __( 'After the content', 'vk-adnetwork' )
                  ),
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
          </figure>


          <p><?php
              // Для блоков размером 970х250 доступно расположение в нижнем колонтитуле страницы.
              esc_html_e( 'For blocks with a size of 970x250, the location in the footer of the page is available.', 'vk-adnetwork' );
              ?>
          </p>

          <figure>
              <?php
              // в нижнем колонтитуле страницы
              echo wp_kses('<img src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-footer.png')
                  . '" title="' . __( 'footer of the page', 'vk-adnetwork' ) . '">',
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
          </figure>


          <p><?php
              // Блоки размером 300х600 и 300х250 можно разместить в боковой панели вашего сайта.
              esc_html_e( 'Blocks of 300x600 and 300x250 sizes can be placed in the sidebar of your site.', 'vk-adnetwork' );
              ?>
          </p>

          <figure>
              <?php
              // в боковой панели
              echo wp_kses('<img src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-widget.png')
                  . '" title="' . __( 'sidebar', 'vk-adnetwork' ) . '">',
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
          </figure>


          <p><?php
              // Для любых размеров и форматов рекламных блоков можно воспользоваться вставкой в любые места страниц через шорткод.
              esc_html_e( 'For all sizes and formats of ad blocks, you can use the insertion of pages in any place via a shortcode.', 'vk-adnetwork' );
              ?>
          </p>

          <figure>
              <?php
              // шорткод
              echo wp_kses('<img border=2 bordercolor=#888 src="' . esc_url(VK_ADNETWORK_BASE_URL . 'admin/assets/img/support/ad-manual.png')
                  . '" title="' . __( 'shortcode', 'vk-adnetwork' ) . '">',
                  ['img' => ['src' => true, 'title' => true]]);
              ?>
          </figure>


          <h3 id="shortcodes"><?php
              // Шорткоды
              esc_html_e( 'Shortcodes', 'vk-adnetwork' );
              ?>
          </h3>

          <p><?php
              // Шорткоды — это самый простой способ вставить рекламу в любом месте статической страницы или поста.
              esc_html_e( 'Shortcodes are the easiest way to insert ads anywhere on a static page or post.', 'vk-adnetwork' );
              ?>
          </p>
          <p><?php
              // <b>«VK AdNetwork»</b> предоставляет короткие коды для рекламных блоков.
              echo wp_kses( __( '<b>VK AdNetwork</b> provides short codes for ad blocks.', 'vk-adnetwork' ),
                  [ 'b' => true ] );
              ?>
          </p>
          <p><?php
              // Шорткоды можно найти на странице <a href="edit.php?post_type=vk_adnetwork" target="_blank">«Реклама»</a>, они имеют следующую структуру:
              echo wp_kses( __( 'The shortcodes can be found on the page <a href="edit.php?post_type=vk_adnetwork" target="_blank">"Advertising"</a>, they have the following structure:', 'vk-adnetwork' ),
                  [ 'a' => ['href' => true, 'target' => true] ] );
              ?>
          </p>
          <p><?php
              // <code>[vk_adnetwork_the_ad id="5"]</code>
              echo wp_kses( '<code>[vk_adnetwork_the_ad id="5"]</code>',
                  [ 'code' => true ] );
              ?>
          </p>
          <p><?php
              // С помощью редактора WordPress можно вставить шорткод в любое место статьи или иной страницы.
              esc_html_e( 'Using the WordPress editor, you can insert a shortcode anywhere in an article or other page.', 'vk-adnetwork' );
              ?>
          </p>

      </div>
    </div>


    <div id="ads-not-showing-up" class="postbox position-full">
      <div class="inside">
          <h3 id="troubleshooting"><?php // Если реклама не отображается
              esc_html_e( 'If the advertisement is not displayed', 'vk-adnetwork' ); ?></h3>
          <!-- <ul>
            <li><?php // На этой странице приведены наиболее распространенные причины отсутствия рекламных блоков.
                    esc_html_e( 'This page lists the most common reasons for the absence of ad blocks.', 'vk-adnetwork' ); ?></li>
            <li><?php // Пожалуйста, проверьте его, прежде чем обращаться к нам за поддержкой.
          esc_html_e( 'Please check it before contacting us for support.', 'vk-adnetwork' ); ?></li>
          </ul> -->
          <h3><?php // Всегда проверяйте это в первую очередь:
              esc_html_e( 'Always check this first:', 'vk-adnetwork' ); ?></h3>

          <p><strong><?php // Отключите все блокировщики рекламы
                  esc_html_e( 'Disable all ad blockers', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Пожалуйста, отключите блокировщики рекламы, а также любые надстройки, такие как Ghostery.
              esc_html_e( 'Please disable ad blockers as well as any add-ons such as Ghostery.', 'vk-adnetwork' ); ?></p>
          <p><?php // Проверьте настройки антивируса. Например, в Антивирусе Касперского есть опция под названием «Антибаннер».
              esc_html_e( 'Check your antivirus settings. For example, Kaspersky Anti-Virus has an option called "AntiBanner".', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Обновите все плагины и WordPress
                  esc_html_e( 'Update all plugins and WordPress', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Обновите до последней версии VK AdNetwork, его надстройки, все остальные плагины и WordPress. Возможно, проблема, с которой вы столкнулись, уже устранена.
              esc_html_e( 'Update to the latest version of VK AdNetwork, its settings, all other plugins and WordPress. Perhaps the problem you are facing has already been fixed.', 'vk-adnetwork' ); ?></p>
          <p><?php // Убедитесь, что в настройках указаны действующие <b>client_id</b> и <b>client_secret</b>.
              echo wp_kses( __( 'Make sure that the current <b>client_id</b> and <b>client_secret</b> are specified in the settings.', 'vk-adnetwork' ), [ 'b' => true ] ); ?></p>

          <p><strong><?php // Попробуйте другой браузер
                  esc_html_e( 'Try another browser', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Проверьте в другом браузере или устройстве. Если там появляется реклама, это может быть вызвано блокировщиком рекламы или аналогичным скриптом. См. выше.
              esc_html_e( 'Check in another browser or device. If an advertisement appears there, it may be caused by an ad blocker or a similar script. See above.', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Отключите кэширование
                  esc_html_e( 'Disable caching', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Все плагины кэширования позволяют отключить кэширование для зарегистрированных пользователей, что является лучшим вариантом для быстрого просмотра изменений.
              esc_html_e( 'All caching plugins allow you to disable caching for registered users, which is the best option for quickly viewing changes.', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Проверьте предупреждения на админ-панели
                  esc_html_e( 'Check the warnings on the admin panel', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Некоторые общие проблемы также упоминаются в верхней части страницы дополнительных настроек рекламы. Просто перейдите в «VK AdNetwork» > «Поддержка» , чтобы увидеть их.
              esc_html_e( 'Some common issues are also mentioned at the top of the advanced ad settings page. Just go to "VK AdNetwork" > "Support" to see them.', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Отключите функции оптимизации кода
                  esc_html_e( 'Disable the code optimization features', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // Некоторые плагины, настройки сервера или пользовательский код «оптимизируют» или минимизируют скрипты или другой вывод внешнего кода. Наиболее распространенный источник конфликта здесь — если они (повторно) перемещают код или скрипты JavaScript. Поскольку большинство рекламных кодов ломаются при таких манипуляциях, функции конфликтуют с ними по замыслу.
              esc_html_e( 'Some plugins, server settings, or custom code "optimize" or minimize scripts or other output from external code. The most common source of conflict here is if they (repeatedly) move JavaScript code or scripts. Since most advertising codes break with such manipulations, the functions conflict with them by design.', 'vk-adnetwork' ); ?></p>
          <p><?php // VK AdNetwork пытается решить такие проблемы автоматически, но мы все равно можем что-то упустить.
              esc_html_e( 'VK AdNetwork tries to solve such problems automatically, but we may still miss something.', 'vk-adnetwork' ); ?></p>
          <p><?php // Плагины или сервисы, которые, как мы уже обнаружили, перемещают (любой) рекламный код со страниц:
              esc_html_e( 'Plugins or services that, as we have already discovered, move (any) advertising code from pages:', 'vk-adnetwork' ); ?></p>
          <ul>
            <li><?php // Автооптимизация
                esc_html_e( 'Auto-optimization', 'vk-adnetwork' ); ?></li>
            <li><?php // CloudFlare — проблемы могут возникнуть при включенном загрузчике Rocket.
                esc_html_e( 'CloudFlare — problems may occur when the Rocket loader is enabled.', 'vk-adnetwork' ); ?></li>
          </ul>
          <p><?php // Мы рекомендуем Autoptimize и WP Rocket для кэширования и оптимизации скорости сайта.
              esc_html_e( 'We recommend Autoptimize and WP Rocket for caching and optimizing site speed.', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Проверьте наличие проблем с JavaScript
                  esc_html_e( 'Check for problems with JavaScript', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // JavaScript — это язык программирования, который используется во многих рекламных кодах. Если другой плагин, тема или пользовательский код вызывают ошибку JavaScript, они также могут нарушить работу другого кода и, следовательно, помешать показу рекламы.
              esc_html_e( 'JavaScript is a programming language that is used in many advertising codes. If another plugin, theme, or custom code causes a JavaScript error, they can also disrupt the operation of other code and, therefore, interfere with the display of ads.', 'vk-adnetwork' ); ?></p>
          <p><?php // <a href="https://codex.wordpress.org/Using_Your_Browser_to_Diagnose_JavaScript_Errors">Эта страница</a> является идеальным ресурсом для всего, что вам нужно знать о том, как выявлять ошибки JavaScript.
              echo wp_kses( __( '<a href="https://codex.wordpress.org/Using_Your_Browser_to_Diagnose_JavaScript_Errors">This page</a> is the perfect resource for everything you need to know about how to detect JavaScript errors.', 'vk-adnetwork' ), [ 'a' => ['href' => true] ] ); ?></p>
          <p><?php // Не каждый конфликт JavaScript должен мешать вашей рекламе, но обычно он нарушает что-то еще, поэтому их исправление в любом случае полезно для вашего сайта.
              esc_html_e( 'Not every JavaScript conflict should interfere with your advertising, but it usually violates something else, so fixing them is useful for your site anyway.', 'vk-adnetwork' ); ?></p>

          <p><strong><?php // Проверьте наличие конфликтов между плагинами и темами
                  esc_html_e( 'Check for conflicts between plugins and themes', 'vk-adnetwork' ); ?></strong></p>
          <p><?php // С десятками тысяч плагинов во вселенной WordPress всегда есть шанс, что некоторые из них не будут работать вместе.
              esc_html_e( 'With tens of thousands of plugins in the WordPress universe, there\'s always a chance that some of them won\'t work together.', 'vk-adnetwork' ); ?></p>
          <p><?php // Попробуйте исключить конфликт плагинов, отключив некоторые из них. Возможно, вы захотите поискать плагины, которые:
              esc_html_e( 'Try to eliminate the conflict of plugins by disabling some of them. You might want to look for plugins that:', 'vk-adnetwork' ); ?></p>
          <ul>
            <li><?php // - изменяют содержание постов и страниц
                esc_html_e( '- change the content of posts and pages', 'vk-adnetwork' ); ?></li>
            <li><?php // - оптимизируют или минимизируют код во внешнем интерфейсе
                esc_html_e( '- optimize or minimize the code in the external interface', 'vk-adnetwork' ); ?></li>
          </ul>
          <p><?php // Вы можете отключить плагины и вернуться к теме по умолчанию только для своей учетной записи, а не для всех остальных на вашем сайте, используя плагин Health Check.
              esc_html_e( 'You can disable plugins and return to the default theme only for your account, and not for everyone else on your site, using the Health Check plugin.', 'vk-adnetwork' ); ?></p>

        </div>
    </div>

    <div id="wp-debug" class="postbox position-full">

      <div class="inside">
        <h3 id="debug_mode"><?php
            // Использование режима отладки рекламы
            esc_html_e( 'Using the ad debugging mode', 'vk-adnetwork' );
            ?>
        </h3>

        <p><?php
            // Режим отладки рекламы помогает вам (и нам) выявить проблемы на вашей странице, которые могут помешать показу рекламы. Режим отладки особенно полезен, если условия отображения не работают должным образом.
            esc_html_e( 'The ad debugging mode helps you (and us) identify problems on your page that may interfere with the display of ads. Debugging mode is especially useful if the display conditions are not working properly.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // Описанные ниже проблемы часто вызваны неработающим кодом в других плагинах или темах и могут вызывать проблемы не только с VK AdNetwork. Поэтому, как правило, рекомендуется их исправлять.
            esc_html_e( 'The problems described below are often caused by broken code in other plugins or themes and can cause problems not only with VK AdNetwork. Therefore, as a rule, it is recommended to correct them.', 'vk-adnetwork' );
            ?>
        </p>


        <h3><?php
            // Включение режима отладки рекламы
            esc_html_e( 'Enabling the ad debugging mode', 'vk-adnetwork' );
            echo ' (WordPress)';
            ?>
        </h3>
        <p><?php
            // Вы можете найти режим отладки в блоке «Режим отладки» на экране редактирования рекламного блока.
            esc_html_e( 'You can find the debugging mode in the "Debugging Mode" section on the ad block editing screen.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // Проставьте галочку в чекбоксе Включить режим отладки (WordPress).
            esc_html_e( 'Check the Enable Debugging Mode (WordPress) checkbox.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // После включения выходные данные рекламного блока будут заменены отладочным содержимым.
            esc_html_e( 'After enabling, the output of the ad block will be replaced with debugging content.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // Кроме того, будут игнорироваться все ограничения показа рекламного блока (условия показа, условия посещения, срок действия).
            esc_html_e( 'In addition, all restrictions on the display of the ad block (display conditions, visit conditions, expiration date) will be ignored.', 'vk-adnetwork' );
            ?>
        </p>
        <p><?php
            // Размер отладочного рекламного блока определяется шириной и высотой, которые вы установили для рекламного блока, и 300×250, если вы их не задали. Содержимое отладочной рекламы можно прокручивать по горизонтали и вертикали.
            esc_html_e( 'The size of the debugging ad block is determined by the width and height that you have set for the ad block, and 300×250 if you have not set them. The content of the debugging ad can be scrolled horizontally and vertically.', 'vk-adnetwork' );
            ?>
        </p>

          <h3><?php
              // Включение режима отладки рекламы
              esc_html_e( 'Enabling the ad debugging mode', 'vk-adnetwork' );
              echo ' (VK AdNetwork)';
              ?>
          </h3>
          <p><?php
              // Вы можете найти режим отладки в блоке «Режим отладки» на экране редактирования рекламного блока.
              esc_html_e( 'You can find the debugging mode in the "Debugging Mode" section on the ad block editing screen.', 'vk-adnetwork' );
              ?>
          </p>
          <p><?php
              // Для тестирования корректности показа рекламы на сайте рекомендуем предварительно использовать режим отладки  (VK AdNetwork)
              esc_html_e( 'To test the correctness of the display of ads on the site, we recommend using the debugging mode (VK AdNetwork) beforehand.', 'vk-adnetwork' );
              ?>
          </p>
          <p><?php
              // Проставьте галочку в чекбоксе Включить режим отладки (VK AdNetwork).
              esc_html_e( 'Check the Enable Debugging Mode (VK AdNetwork) checkbox.', 'vk-adnetwork' );
              ?>
          </p>
          <p><?php
              // После включения галочки реклама будет транслироваться в тестовом режиме, без начисления показов и денег.
              esc_html_e( 'After enabling the check mark, the advertisement will be broadcast in test mode, without accrual of impressions and money.', 'vk-adnetwork' );
              ?>
          </p>

      </div>
    </div>

  </div>
</div>
