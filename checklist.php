<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$pageTitle = '계약 전 체크리스트 | 부커리';
$boardSlug = 'house';
ob_start();
?>
<article class="info-page" style="max-width:720px;margin:0 auto;padding:16px 18px 48px;font-size:14px;line-height:1.6;color:#1b1f2a;">
    <p style="margin:0 0 12px"><a href="index.php">← 홈</a></p>
    <h1 style="font-size:22px;margin:0 0 8px">계약 전 체크리스트</h1>
    <p style="color:#5a6270;margin:0 0 20px">부동산 계약·전세·월세 전에 흔히 놓치는 확인 순서를 정리했습니다. 커뮤니티 글과 함께 참고하세요.</p>

    <ol style="padding-left:1.2em;margin:0 0 24px">
        <li style="margin-bottom:10px"><strong>등기부등본</strong> — 소유자, 근저당·가압류 등 권리 관계 확인</li>
        <li style="margin-bottom:10px"><strong>실제 점유</strong> — 현장 방문, 호수·키·세대주 확인</li>
        <li style="margin-bottom:10px"><strong>보증 구조</strong> — 전세보증보험·보증금 반환 조건·특약 문구</li>
        <li style="margin-bottom:10px"><strong>입금 계좌</strong> — 명의·통장 주인이 계약 당사자와 일치하는지</li>
        <li style="margin-bottom:10px"><strong>중개 확인</strong> — 개업·등록 여부, 중개보수·광고 책임</li>
        <li style="margin-bottom:10px"><strong>증거 남기기</strong> — 문자·통화 요약, 계약서 사본·영수증 보관</li>
    </ol>

    <p style="margin:0"><a href="mustknow.php">필수 확인 정보</a> · <a href="index.php?b=contract">계약 고민 게시판</a></p>
</article>
<?php
$content = ob_get_clean();
require __DIR__ . '/templates/shell.php';
