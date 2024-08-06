<div id="home">
  <div class="d-flex flex-column align-items-stretch">
      <a href="/" class="d-flex align-items-center flex-shrink-0 p-3 link-body-emphasis text-decoration-none border-bottom">
        <svg class="bi pe-none me-2" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
        <span class="fs-5 fw-semibold">Factures</span>
      </a>
      <div class="list-group list-group-flush border-bottom">
        <?php foreach($pieces as $line): ?>
          <?php if (!$line->banque_id): ?>
          <a href="/associate?date=<?php echo $line->facture_date?>&libelle=<?php echo $line->facture_libelle;?>&amount=<?php echo abs($line->facture_prix_ttc); ?>" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
          <?php else: ?>
          <a href="/piece/<?php echo $line->piece_id; ?>" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
          <?php endif; ?>
          <div class="d-flex w-100 align-items-center justify-content-between">
            <strong class="mb-1">
              <?php echo $line->facture_author; ?>
               ->
              <?php echo $line->facture_client; ?>
            </strong>
            <strong><?php echo ($line->facture_prix_ht) ? number_format($line->facture_prix_ht, 2, ',', ' ').' € HT' : '<span class="badge text-bg-danger rounded-pill">non saisi</span>'; ?></strong>
          </div>
          <div class="row">
            <div class="col-2 mb-1 small"><?php echo $line->facture_date; ?></div>
            <div class="col-6 mb-1 small"><?php echo $line->facture_libelle; ?></div>
            <div class="col-2 text-center">
              <?php if (!$line->banque_id): ?>
              <span class="badge text-bg-secondary rounded-pill">Paiement manquant</span>
              <?php else: ?>
                <span class="badge text-bg-success rounded-pill">Paiement OK</span>
              <?php endif; ?>
            </div>
            <div class="col-2 text-end">
              <small><?php echo ($line->facture_prix_ttc) ? number_format($line->facture_prix_ttc, 2, ',', ' ').' € TTC' : 'non saisi'; ?></small>
            </div>
          </div>
          </a>
        <?php endforeach; ?>
      </div>
  </div>
</div>
