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
            <a href="/banque" class="nav-link active">
              Banque
            </a>
          </li>
          <li>
            <a href="/pieces" class="nav-link link-body-emphasis">
              Factures émises
            </a>
          </li>
          <li>
            <a href="/files" class="nav-link link-body-emphasis">
              Le justificatifs
            </a>
          </li>
        </ul>
        <hr>
      </div>
      <div>
          <?php include __DIR__.'/'.Base::instance()->get('content') ?>
      </div>
    </main>
    <footer>
      <p>footer</p>
    </footer>
  </body>
</html>
