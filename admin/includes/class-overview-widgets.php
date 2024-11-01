<?php
/**
 * Container class for callbacks for overview widgets
 *
 * @package WordPress
 * @subpackage VK AdNetwork Plugin
 */
class VK_Adnetwork_Overview_Widgets_Callbacks {

    /**
     * Register the plugin overview widgets
     */
    public static function setup_overview_widgets() {

        self::add_meta_box(
            'vk_adnetwork_overview_news',
            esc_html__( 'Advertising statistics on site', 'vk-adnetwork' ),
            'top',
            'render_graph'
        );

        do_action( 'vk-adnetwork-overview-widgets-after' );
    }

    /**
     * Loads a meta box into output
     *
     * @param string   $id meta box ID.
     * @param string   $title title of the meta box.
     * @param string   $position context in which to show the box.
     * @param callable $callback function that fills the box with the desired content.
     */
    public static function add_meta_box( $id, $title, $position, $callback ) {
        ob_start();
        call_user_func( [ 'VK_Adnetwork_Overview_Widgets_Callbacks', $callback ] );
        do_action( 'vk-adnetwork-overview-widget-content-' . $id, $id );
        $content = ob_get_clean();

        include VK_ADNETWORK_BASE_PATH . 'admin/views/overview-widget.php';
    }

    /**
     * Render next steps widget
     */
    public static function render_graph() {

        $statfields = [
            'requests'          => esc_html__('Requests' , 'vk-adnetwork'),  // 'Запросы'      'кол-во запросов'
            // 'responses'         => esc_html__('Responses', 'vk-adnetwork'),  // 'Ответы'       'кол-во баннеров, подобранных на запросы'
            // 'fill_rate'         => esc_html__('Fill rate', 'vk-adnetwork'),  // 'Fill Rate, %' 'заполняемость' = 100*responses/requests
            // 'show_rate'         => esc_html__('Show rate', 'vk-adnetwork'),  // 'Show Rate, %' 'процент показов к количеству ответов' = 100*shows/responsed = 100*shows/responsed_blocks
            // 'shows'             => esc_html__('Shows'    , 'vk-adnetwork'),  // 'Показы'       'кол-во показов объявлений'
            'amount'            => esc_html__('Amount'   , 'vk-adnetwork'),  // 'Доход'        'сумма начислений за показы объявлений'
            // 'cpm'               => esc_html__('CPM'      , 'vk-adnetwork'),  // 'CPM, ₽'       'денег/показам*1000'
//            'requested_banners' => 'Запрошенные баннеры',  // 'кол-во запросов баннеров'
//            'responsed_blocks'  => 'Отвеченные баннеры' ,  //  banners_in_responses?
//            'clicks'            => 'Клики'              ,  // 'кол-во кликов по объявлению'
//            'ctr'               => 'CTR, %'             ,  // 'click-through rate'
        ];
        // ХЗ (https://ads.vk.com/partner/groups)  eCPM, ₽ (денег/показам*1000); Доля полных просмотров, % (к видео)
        // ХЗ (json) confirmed_win_notice display_rate goals loss_notice loss_rate render win_notice win_rate
        // https://target.my.com/help/partners/web/reporting_api_statistics/ru
        // cpm - средняя цена за 1000 показов
        // noshows - кол-во запрашиваемых баннеров, для которых не было подобрано объявление
        // vtr - процентное завершённых просмотров к количеству запусков видео
        // vr - процент просмотров по данным MRC

        $data = VK_Adnetwork_Utils::vk_adnetwork_group_stat_pads(7);
        // ['graph' => $data, 'deliverygroups' => $items];
        if (!$data) return;
        $options = VK_Adnetwork::get_instance()->options();
        $group_id = $options['group_id'] ?? '';
        echo wp_kses(
                '<div class="block">'
                // Динамика изменения основных метрик вашей рекламы.
                . __('Dynamics of changes in the main metrics of your advertising.', 'vk-adnetwork')
                . '<br />'
                . sprintf( // Подробные отчёты можно скачать <a href="%s">в разделе «Статистика» партнёрской сети</a>
                    // translators: %s is the address of the statistics page.
                    __('Detailed reports can be downloaded <a href="%s" target="_blank">in the Statistics section of the partner network</a>', 'vk-adnetwork'),
                    VK_ADNETWORK_URL .'hq/partner/statistics' // -x- 'partner/groups/' . intval($group_id) . '/pads'
                )
                . '</div>'
                . '<div class="separator"></div>',
            ['a' => ['href'=> true, 'target'=> true], 'div' => ['class' => true], 'br' => true]
        );
        $deliverygroups = $data['deliverygroups']['group_pads'] ?? [];

        $delivery['delivering']      = _x('delivering',      'group_pad', 'vk-adnetwork'); // транслируется
        $delivery['test_delivering'] = _x('test_delivering', 'group_pad', 'vk-adnetwork'); // тестовая трансляция
        $delivery['not_delivering']  = _x('not_delivering',  'group_pad', 'vk-adnetwork'); // не транслируется
        $status['active']            = _x('active',          'group_pad', 'vk-adnetwork'); // площадка активна
        $status['blocked']           = _x('blocked',         'group_pad', 'vk-adnetwork'); // площадка остановлена               ~ GROUP_PAD_STOPPED
        $status['deleted']           = _x('deleted',         'group_pad', 'vk-adnetwork'); // площадка заархивирована (удалена)  ~ GROUP_PAD_ARCHIVED
        foreach ($deliverygroups as $groupid => $group) {
            if ($groupid != $group_id) continue; //
            // Статус трансляции:
            echo wp_kses(
                    '<div class="block"><div>' . esc_html__('Delivery status', 'vk-adnetwork')
                    . ': <b>'
                    . esc_html($delivery[$group['delivery']] ?? $group['delivery'])
                    . '</b></div><div>',
                ['div' => ['class' => true], 'b' => true]
            );

            // Статус:
            ;
            echo wp_kses(
                    esc_html__('Group status', 'vk-adnetwork')
                    . ': <b>'
                    . esc_html($status[$group['status']] ?? $group['status'])
                    . '</b></div><div>'
                    // Площадка
                    //esc_html_e('Website', 'vk-adnetwork'); echo ": $groupid <b><a href='https://target.my.com/partner/groups/$groupid/pads'>$group[description]</a></b></div>";   // <a href>WP: dmlihachev.h1n.ru</a>
                    . '</div>',
                ['div' => ['class' => true], 'b' => true]
            );
            // Урл убираем -- нах он нужен? ))
            // if (stripos($group['url'], 'http') !== 0) $group['url'] = "//$group[url]";
            // echo " (<a href='$group[url]'>$group[url]</a>)<p>";                                                 // (<a href>//dmlihachev.h1n.ru</a>)

            if ($group['issues']) {
                echo wp_kses('<div class="block"><u><b>'
                    . esc_html__('Issues', 'vk-adnetwork')
                    .'</b></u>:<ol> ',
                    ['div' => ['class' => true], 'b' => true, 'u' => true, 'ol' => true]
                );
                $codes['GROUP_PAD_ON_MODERATION']                          = __('GROUP_PAD_ON_MODERATION',                          'vk-adnetwork'); // Площадка только что создана и ещё не прошла модерацию.       The GroupPad is on moderation.
                $codes['NO_ACTIVE_PADS']                                   = __('NO_ACTIVE_PADS',                                   'vk-adnetwork'); // Нет активных транслируемых блоков.                           The GroupPad has no active pads.
                $codes['GROUP_PAD_BANNED']                                 = __('GROUP_PAD_BANNED',                                 'vk-adnetwork'); // Площадка забанена на модерации.                              The GroupPad is rejected by moderation.
                $codes['GROUP_PAD_STOPPED']                                = __('GROUP_PAD_STOPPED',                                'vk-adnetwork'); // Площадка остановлена.                                        The GroupPad is stopped.
                $codes['PARTNER_IS_NOT_APPROVED']                          = __('PARTNER_IS_NOT_APPROVED',                          'vk-adnetwork'); // Партнёр не прошёл проверку.                                  Partner is not approved.
                $codes['GROUP_PAD_ARCHIVED']                               = __('GROUP_PAD_ARCHIVED',                               'vk-adnetwork'); // Площадка заархивирована (удалена).                           The GroupPad is removed.
                $codes['PAD_ARCHIVED']                                     = __('PAD_ARCHIVED',                                     'vk-adnetwork'); // Блок заархивирован (удален).                                 Placement is archived.
                $codes['PAD_BANNED']                                       = __('PAD_BANNED',                                       'vk-adnetwork'); // Блок заблокирован.                                           Placement is blocked.
                $codes['PAD_ON_MODERATION']                                = __('PAD_ON_MODERATION',                                'vk-adnetwork'); // Блок на модерации.                                           Placement on moderation.
                $codes['PAD_STOPPED']                                      = __('PAD_STOPPED',                                      'vk-adnetwork'); // Блок остановлен.                                             Placement is stopped.
                $codes['UNDEFINED']                                        = __('UNDEFINED',                                        'vk-adnetwork'); // Неизвестно.                                                  Undefined.
                $codes['USER_INACTIVE']                                    = __('USER_INACTIVE',                                    'vk-adnetwork'); // Пользователь не активен.                                     User is not active.
                $codes['GROUP_PAD_MODERATION_REASON_BAD_CONTENT']          = __('GROUP_PAD_MODERATION_REASON_BAD_CONTENT',          'vk-adnetwork'); // Контент площадки не соответствует правилам Таргета
                $codes['GROUP_PAD_MODERATION_REASON_PARTNER_IS_NOT_OWNER'] = __('GROUP_PAD_MODERATION_REASON_PARTNER_IS_NOT_OWNER', 'vk-adnetwork'); // Мы сомневаемся, что указанный веб-сайт / приложение принадлежит вам. Чтобы разблокировать веб-сайт / приложение, пожалуйста, подтвердите, что вы являетесь владельцем веб-сайта / приложения, и предоставьте подтверждающие документы. // We doubt that the specified website / app belongs to you. To unblock the website / app, please confirm you own the website / app and provide supporting documents.
                $codes['GROUP_PAD_MODERATION_REASON_CUSTOM']               = __('GROUP_PAD_MODERATION_REASON_CUSTOM',               'vk-adnetwork'); // Площадка не найдена
                $codes['GROUP_PAD_MODERATION_REASON_LAW']                  = __('GROUP_PAD_MODERATION_REASON_LAW',                  'vk-adnetwork'); // Площадка не соответствует требованиям действующего законодательства РФ и/или правилам VK AdNetwork.

                foreach ($group['issues'] as $code => $message) {
                    echo wp_kses(
                        '<li>' . esc_html($codes[$code]) . ' <font color="#FFFFFF">' . esc_html($message) . '</font></li>',
                        ['li' => true, 'font' => ['color' => true]]
                    ); // МЕСИДЖ не переводим, только КОД
                }
                echo wp_kses('</ol></div>', ['div' => true, 'ol' => true]);
            }elseif ($group['delivery'] === 'delivering') { // т.е. delivering + пустой массив issue
                // Площадка прошла модерацию и у неё есть блоки, прошедшие модерацию и транслируемые.
                echo wp_kses(
                    '<div class="block">'
                    . esc_html__('The site has been moderated. There are moderated and broadcast ad units.', 'vk-adnetwork')
                    . '</div>',
                    ['div' => ['class' => true]]
                );
            }

        }
        $items = $data['graph']['items'] ?? '';
        if (!$items) { // Первые насколько дней мы будем собирать статистику, чтобы показать достоверные данные. Графики скоро появятся. // НЕТ графиков!
            echo wp_kses(
                '<div class="block">'
                . esc_html__('For the first few days we will collect statistics to show reliable data. Charts are coming soon.', 'vk-adnetwork')
                . '</div>',
                ['div' => ['class' => true]]
            );
            // echo '</h3><pre>'; print_r($data['graph']); echo '</pre>';
            return;
        }

        $ids = [0];
        $total = [0];
        foreach ($items as $item) {
            $rows = $item['rows'];
            $ids[] = $id = $item['id'];
            $txt[$id] = $item['txt'];
            foreach ($rows as $r) {
                $labels[0][$r['date']] = $labels[$id][$r['date']] = "'$r[date]'";
                foreach (array_keys($statfields) as $statfield) {
                    $graph[$id][$statfield][$r['date']] = $r[$statfield]; // eCPM cpm = 1000 * amount / shows // одно вычисляемое поле: (денег/показам*1000)
                    $graph[0][$statfield][$r['date']] ??= 0;
                    $graph[0][$statfield][$r['date']] += $r[$statfield];
                    $total[$id] ??= 0;
                    $total[$id] += $r[$statfield];
                    $total[0] += $r[$statfield];
                }
            }
        }
        if (!$total[0]) { // Первые насколько дней мы будем собирать статистику, чтобы показать достоверные данные. Графики скоро появятся. // НЕТ графиков!
            echo wp_kses(
                '<div class="block">'
                . esc_html__('For the first few days we will collect statistics to show reliable data. Charts are coming soon.', 'vk-adnetwork')
                . '.</div>',
                ['div' => ['class' => true]]
            );
            return;
        }

        foreach ($ids as $id) {
            if ($total[$id])
                /** @noinspection PhpUndefinedVariableInspection */
                self::view_one_graph($id, $statfields, $graph[$id], $labels[$id], $txt[$id] ?? [], $groupid);
        }

        self::view_graph($groupid);
    }

    /* Здесь рисуем кнопку и канвас для графика, по 1 на каждую площадку.
     * js placed at assets/js/npm-chart.js
     * see class-vk-adnetwork-admin.php > function enqueue_admin_scripts > wp_enqueue_script(plugin_slug . '-ad-chart-script' ..
     */
    public static function view_graph ($groupId) {
        ?>
            <div style="max-width: 1000px; font-family: monospace;">
                <div style="position: relative; padding-bottom: 50%"><canvas id="chart_<?php echo esc_html($groupId) ?>" class="canvaschart"
                ></canvas></div>
            </div>
            <style>.canvaschart {position: absolute;}</style>

        <?php
            ob_start(); // script
        ?>
                var canvas = document.querySelector('#chart_<?php echo esc_html($groupId) ?>');
                var currentChart = null;

                function initChart(labels, datasets, title) {
                    console.log(arguments);
                    if (currentChart) {
                        currentChart.destroy();
                    }
                    currentChart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels,
                            datasets,
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        boxHeight: 2,
                                        borderRadius: 0,
                                    }
                                },
                                title: {
                                    display: true,
                                    text: title,
                                }
                            }
                        },

                    });
                }

                function getCurrentChartData(optionEl) {
                    if (!optionEl) return;
                    
                    var blockId = optionEl.value;

                    var groupData = window.chartData && window.chartData[<?php echo esc_html($groupId) ?>];

                    if (!groupData) return;

                    return groupData[blockId];
                }

                function initCurrentBlockChart(el) {
                    var chartData = getCurrentChartData(el);

                    if (!chartData) return;

                    initChart(chartData.labels, chartData.datasets, chartData.title);
                }

                Array.prototype.slice.call(document.querySelectorAll('input[name^="chart_option_"]')).forEach((el) => {
                    el.addEventListener('click', () => {
                        initCurrentBlockChart(el);
                    })
                });

                initCurrentBlockChart(document.querySelector('input[name="chart_option_<?php echo esc_html($groupId) ?>"]:checked'));
        <?php
        wp_add_inline_script(VK_ADNETWORK_SLUG . '-ad-chart-script', ob_get_clean()); // /script
    }

    // Здесь выводим блок в виде опции группы радио-баттонов + складываем данные для него в 
    // глобальный объект с группировкой по площадке и блоку.
    public static function view_one_graph ($pad_id, $statfields, $data, $labels, $txt, $groupid) {
        ?>
        <div class="block">
            <div class="stat-header">
            <label class="stat-header-title">
                <input
                    <?php echo esc_html($pad_id == 0 ? 'checked': ''); ?>
                    type="radio"
                    name="chart_option_<?php echo esc_html($groupid) ?>"
                    value="<?php echo esc_html($pad_id) ?>"
                />
            <?php

            $delivery['delivering']      = __('delivering',      'vk-adnetwork'); // транслируется
            $delivery['test_delivering'] = __('test_delivering', 'vk-adnetwork'); // тестовая трансляция
            $delivery['not_delivering']  = __('not_delivering',  'vk-adnetwork'); // не транслируется
            $status['active']            = __('active',          'vk-adnetwork'); // активен
            $status['blocked']           = __('blocked',         'vk-adnetwork'); // остановлен
            $status['deleted']           = __('deleted',         'vk-adnetwork'); // заархивирован (удалён)


            // тут м.б. ИЛИ блок (на нашей площадке есть графики), ИЛИ площадка (на нашей нет графиков, берём список) -- поэтому без конкретики перевод
            if ($pad_id) {
                $paddescr3 = join(', ', array_filter([                      // это ёпть такой в пихапе способ выкинуть пустые "" из массива ))
                    ($txt['description'] ?? ''),
                    ($status[$txt['status']] ?? $txt['status'] ?? ''),
                    ($delivery[$txt['delivery']] ?? $txt['delivery'] ?? ''),
                ]));
                echo esc_html(__('Pad', 'vk-adnetwork') . " $pad_id ($paddescr3)");
            }else
                esc_html_e('All pads', 'vk-adnetwork');
            ?>
            </label>
        </div>
        <?php
            ob_start(); // script
        ?>
            var chartData = window.chartData || {};
            chartData[<?php echo esc_html($groupid) ?>] = chartData[<?php echo esc_html($groupid) ?>] || {};
            chartData[<?php echo esc_html($groupid) ?>][<?php echo esc_html($pad_id) ?>] = {
                labels: [ <?php // $labels = [«'YYYY-MM-DD'», ..] ~> labels: [ 'YYYY-MM-DD', 'YYYY-MM-DD', ..
                    echo wp_kses_post(join(', ', $labels));
                ?> ],
                datasets: [
                    <?php foreach (array_keys($statfields) as $statfield) { ?>
                        {
                            label: '<?php echo esc_html($statfields[$statfield]) ?>',
                            data: [ <?php echo esc_html(join(', ', $data[$statfield])); ?> ],
                        },
                    <?php } ?>
                ],
                title: '<?php
                    // translators: %s is the number of Pad.
                    echo $pad_id ? sprintf( esc_html__( 'Pad %s statistic', 'vk-adnetwork' ), absint($pad_id) ) : esc_html__('Statistic (all pads)', 'vk-adnetwork');
                ?>'
            };
            window.chartData = chartData;
        <?php
        wp_add_inline_script(VK_ADNETWORK_SLUG . '-ad-chart-script', ob_get_clean()); // /script
    }

}
