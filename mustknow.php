<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$pageTitle = '필수 확인 정보 | 부커리';
$boardSlug = 'house';
ob_start();
?>
<article class="info-page" style="max-width:720px;margin:0 auto;padding:16px 18px 48px;font-size:14px;line-height:1.6;color:#1b1f2a;">
    <p style="margin:0 0 12px"><a href="index.php">← 홈</a></p>
    <h1 style="font-size:22px;margin:0 0 8px">필수 확인 정보</h1>
    <p style="color:#5a6270;margin:0 0 20px">분쟁을 줄이기 위해 반드시 챙기면 좋은 정보입니다. 지역·매물마다 다를 수 있으니 필요 시 전문가 상담을 병행하세요.</p>

    <h2 style="font-size:17px;margin:24px 0 8px">서류·등기</h2>
    <ul style="padding-left:1.2em;margin:0 0 16px">
        <li>등기부등본 발급일이 너무 오래되지 않았는지</li>
        <li>임대인 신분증·인감(또는 본인서명) 확인</li>
    </ul>

    <h2 style="font-size:17px;margin:24px 0 8px">금전·계약</h2>
    <ul style="padding-left:1.2em;margin:0 0 16px">
        <li>계약서 금액·선·잔금 일정·계좌가 명확한지</li>
        <li>위약·해지·원상복구 특약이 공정한지</li>
    </ul>

    <p style="margin:0"><a href="checklist.php">체크리스트</a> · <a href="index.php?b=rent">전세·월세 게시판</a></p>
</article>
<?php
$content = ob_get_clean();
require __DIR__ . '/templates/shell.php';
