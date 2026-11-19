<?php $pager->setSurroundCount(2) ?>

<style>
    /* Forzar el diseño de los botones */
    .tudu-pagination {
        display: flex !important;
        gap: 8px;
        list-style: none !important;
        padding: 0 !important;
        margin: 25px 0 !important;
        justify-content: center;
    }
    .tudu-pagination li {
        display: inline-block;
        list-style: none !important;
    }
    .tudu-pagination a {
        display: block;
        padding: 8px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        color: #1565c0;
        text-decoration: none;
        font-weight: bold;
        background-color: #ffffff;
        transition: all 0.2s;
    }
    .tudu-pagination a:hover {
        background-color: #f1f5f9;
        border-color: #1565c0;
    }
    .tudu-pagination li.active a {
        background-color: #1565c0;
        color: white;
        border-color: #1565c0;
        pointer-events: none;
    }
</style>

<nav aria-label="Page navigation">
    <ul class="tudu-pagination">
        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getPrevious() ?>" aria-label="Previous">&laquo;</a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="<?= $link['active'] ? 'active' : '' ?>">
                <a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>" aria-label="Next">&raquo;</a>
            </li>
        <?php endif ?>
    </ul>
</nav>