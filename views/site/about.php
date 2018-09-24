<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Личный кабинет';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-private-room">
    <h1><?= Html::encode($this->title) ?></h1>
    Ваша реферальная ссылка: <?= $referal_link ?>
</div>
<hr>
<div>
    <?php if ($ref_email != ''): ?>
        <p>вы пришли от <?= $ref_email ?></p>
    <?php endif;?>
</div>
<hr>
<div>
    Вы пригласили:
    <?php foreach ($children as $child): ?>
        <p>- <?= $child ?></p>
    <?php endforeach;?>
</div>
