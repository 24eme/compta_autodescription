<div id="home">
    <div class="d-flex flex-column">
        <a href="/" class="d-flex align-items-center flex-shrink-0 p-3 link-body-emphasis text-decoration-none border-bottom">
          <svg class="bi pe-none me-2" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
          <span class="fs-5 fw-semibold">Les pièces</span>
        </a>
        <div class="list-group list-group-flush border-bottom scrollarea">
          <?php foreach($files as $line): ?>
            <?php if (!$line->piece_id): ?>
            <a href="/associate?date=" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
            <?php else: ?>
            <a href="/piece/<?php echo $line->piece_id; ?>" class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true">
            <?php endif; ?>
            <div class="d-flex w-100 align-items-center justify-content-between">
              <strong class="mb-1"><?php echo $line->filename; ?></strong>
            </div>
            <div class="row">
              <div class="col-2 text-center"><?php echo date('Y-m-d', $line->ctime); ?></div>
              <div class="col-8 mb-1 small"><?php echo $line->fullpath;?></div>
              <div class="col-2 text-center">
                <?php if (!$line->piece_id): ?>
                <span class="badge text-bg-secondary rounded-pill">Pièce manquante</span>
                <?php else: ?>
                  <span class="badge text-bg-success rounded-pill">Pièce saisie</span>
                <?php endif; ?>
              </div>
            </div>
            </a>
          <?php endforeach; ?>
        </div>
    </div>
</div>
