<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Empty</title>

    <link href='/css/bootstrap.5.3.3.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css" />
</head>
<body>
  <main class="d-flex flex-nowrap">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
          <span class="fs-4">Compta par les pièces</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
          <li class="nav-item">
            <a href="/banque" class="nav-link<?php if (Base::instance()->get('sidebar.actif') == 'banque') echo ' active'; ?>">
              Banque
            </a>
          </li>
          <li>
            <a href="/pieces" class="nav-link<?php if (Base::instance()->get('sidebar.actif') == 'piece') echo ' active'; ?>">
              Factures émises
            </a>
          </li>
          <li>
            <a href="/files" class="nav-link<?php if (Base::instance()->get('sidebar.actif') == 'file') echo ' active'; ?>">
              Le justificatifs
            </a>
          </li>
        </ul>
        <hr>
      </div>
      <div class="d-flex flex-column" style="flex: 0 0 80%; min-height: 1000px">
          <?php include __DIR__.'/'.Base::instance()->get('content') ?>
      </div>
    </main>
    <footer>
      <p>footer</p>
    </footer>
  </body>
</html>
