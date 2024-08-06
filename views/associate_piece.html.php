<div id="home">
  <div class="d-flex flex-column align-items-stretch">
    <a href="/" class="p-3 link-body-emphasis text-decoration-none border-bottom">
      <div class="d-flex w-100 align-items-center justify-content-between">
      <strong><?php echo ($piece) ? $piece->facture_client.' : '.$piece->facture_libelle : $file->filename;?></strong>
      <strong><?php echo ($piece) ? $piece->facture_prix_ttc : 'non saisi';?> €</strong>
      </div>
      <div class="row">
      <div class="col-2"><p><?php echo date('Y-m-d', $file->ctime); ?></p></div>
      <div class="col-8 text-muted"><p><?php echo $file->fullpath; ?></p></div>
      </div>
    </a>
    <div class="list-group list-group-flush border-bottom">
    <?php foreach($banques as $b): ?>
    <a href="#" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
    <div class="d-flex w-100 align-items-center justify-content-between">
      <strong class="mb-1">
        <?php echo $b['line']->label; ?>
      </strong>
      <strong><?php echo $b['line']->amount; ?> €</strong>
    </div>
    <div class="row">
      <div class="col-2"><?php echo $b['line']->date; ?></div>
      <div class="col-8 text-muted">
        <?php echo $b['line']->raw; ?>
      </div>
    </div>
    </a>
    <?php endforeach; ?>
    </div>
  </div>
</div>
