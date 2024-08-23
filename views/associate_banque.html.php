<div id="home">
  <div class="d-flex flex-column align-items-stretch">
    <a href="/" class="p-3 link-body-emphasis text-decoration-none border-bottom">
      <h3 class="p-3">Activité Bancaire : suivi d'un paiement</h3>
      <div class="d-flex w-100 align-items-center justify-content-between">
      <strong><?php echo $banque_line->label;?></strong>
      <strong><?php echo $banque_line->amount;?> €</strong>
      </div>
      <div class="row">
      <div class="col-6"><p><?php echo $banque_line->date; ?></p></div>
      </div>
    </a>
    <div class="list-group list-group-flush border-bottom">
    <?php foreach($pieces as $p): ?>
    <a href="?banque_id=<?php echo $banque_line->id; ?>&file_md5=<?php echo $p['piece']->md5; ?>" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
    <div class="d-flex w-100 align-items-center justify-content-between">
      <strong class="mb-1">
        <?php echo $p['piece']->facture_client; ?> - <?php echo $p['piece']->facture_libelle; ?>
      </strong>
      <strong><?php echo $p['piece']->facture_prix_ttc; ?> €</strong>
    </div>
    <div class="row">
      <div class="col-2"><?php echo $p['piece']->facture_date; ?></div>
      <div class="col-8 text-muted">
        <?php if (isset($p['piece']->paiement_comment) && $p['piece']->paiement_comment): ?>
        <?php echo $p['piece']->paiement_comment; ?> -
        <?php endif; ?>
        <?php echo $p['piece']->filename; ?>
      </div>
    </div>
    </a>
    <?php endforeach; ?>
    <?php foreach($files as $f): ?>
      <a href="?banque_id=<?php echo $banque_line->id; ?>&file_md5=<?php echo $f['file']->md5; ?>" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
    <div class="d-flex w-100 align-items-center justify-content-between">

      <strong class="mb-1">
        <?php echo $f['file']->filename; ?>
      </strong>
      <strong>Montant non saisi</strong>
    </div>
    <div class="row">
      <div class="col-2"><?php echo date('Y-m-d', $f['file']->ctime); ?></div>
      <div class="col-8 text-muted">
        <?php echo $f['file']->fullpath; ?>
      </div>

    </div>
    </a>
    <?php endforeach; ?>
    </div>
  </div>
</div>
