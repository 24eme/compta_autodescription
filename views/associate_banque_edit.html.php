<div id="home">
  <div class="d-flex flex-column align-items-stretch" style="height: 1000px">
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
    <div class="list-group" style="height: 100%">
      <iframe src="<?php echo $editmeta['url'].str_replace($editmeta['prefix'], '', $file->fullpath); ?>" style="height: 100%">
      </iframe>
    </div>
</div>
