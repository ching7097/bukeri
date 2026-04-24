<?php
declare(strict_types=1);

/**
 * @return array{enabled?: bool, client?: string, slot?: string}
 */
function adsense_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $path = __DIR__ . '/../config/adsense.php';
        $cfg = is_file($path) ? require $path : ['enabled' => false];
    }
    return is_array($cfg) ? $cfg : ['enabled' => false];
}

function adsense_is_enabled(): bool
{
    return !empty(adsense_config()['enabled']);
}

/**
 * @param string      $placement  CSS modifier for .adsense-wrap--{placement}
 * @param string|null $outerClass Optional wrapper div class(es); omitted entirely when ads off
 */
function adsense_render(string $placement = 'default', ?string $outerClass = null): void
{
    if (!adsense_is_enabled()) {
        return;
    }

    $cfg = adsense_config();
    $client = (string) ($cfg['client'] ?? 'ca-pub-8573716814546415');
    $slot = (string) ($cfg['slot'] ?? '9429666139');

    $safe = preg_replace('/[^a-z0-9_-]/i', '', $placement);
    if ($safe === '') {
        $safe = 'default';
    }

    $wrap = $outerClass !== null ? trim($outerClass) : '';
    if ($wrap !== '') {
        echo '<div class="' . h($wrap) . '">';
    }
    ?>
    <div class="adsense-wrap adsense-wrap--<?= h($safe) ?>" role="complementary" aria-label="광고">
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="<?= h($client) ?>"
            data-ad-slot="<?= h($slot) ?>"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
    </div>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    <?php
    if ($wrap !== '') {
        echo '</div>';
    }
}
