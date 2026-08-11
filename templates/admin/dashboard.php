<?php declare(strict_types=1); ?>
<h1>Dashboard</h1>
<div class="grid">
    <div class="card"><div class="muted">Planos</div><div class="stat"><?= (int) $plans ?></div></div>
    <div class="card"><div class="muted">Assinaturas</div><div class="stat"><?= (int) $subscriptions ?></div></div>
    <div class="card"><div class="muted">Boxes</div><div class="stat"><?= (int) $boxes ?></div></div>
    <div class="card"><div class="muted">Contatos</div><div class="stat"><?= (int) $contacts ?></div></div>
</div>
