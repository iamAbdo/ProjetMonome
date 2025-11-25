<?php
declare(strict_types=1);
$pageTitle = 'Template — Catalogue';
require __DIR__ . '/includes/header.php';

$res = $connection->query("SELECT id, name, LOWER(REPLACE(name, ' ', '-')) AS slug FROM categories ORDER BY name ASC");
$categories = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$pRes = $connection->query("SELECT p.*, c.name AS category_name, LOWER(REPLACE(IFNULL(c.name, 'collection'), ' ', '-')) AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC");
$products = $pRes ? $pRes->fetch_all(MYSQLI_ASSOC) : [];
?>

<main>
    <div class="section-title">
        <div>
            <p style="color: var(--muted); text-transform: uppercase; letter-spacing: 0.2em; margin: 0;">Catalogue</p>
            <h2>Nos pièces signatures</h2>
        </div>
        <p style="max-width: 360px; color: var(--muted);">Cliquez sur « Commander » pour pré-réserver l’article. Aucun paiement en ligne, nous vous rappelons pour finaliser.</p>
    </div>

    <div class="section-title" style="margin-top: 0;">
        <div class="chip-group">
            <span class="chip active" data-filter="all">Tous</span>
            <?php foreach ($categories as $cat): ?>
                <span class="chip" data-filter="<?= sanitize($cat['slug']); ?>"><?= sanitize($cat['name']); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <section class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-body empty-state">
                    Aucun produit n’a encore été ajouté. Revenez bientôt.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <article class="card" data-category="<?= sanitize($product['category_slug']); ?>">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= sanitize($product['image']); ?>" alt="<?= sanitize($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.2em;">
                            <?= sanitize($product['category_name'] ?? 'Collection Template'); ?>
                        </p>
                        <h3><?= sanitize($product['name']); ?></h3>
                        <p style="color: var(--muted);"><?= sanitize($product['description'] ?? 'Pièce iconique imaginée pour une expérience sensuelle et durable.'); ?></p>
                        <div class="price"><?= number_format((float)$product['price'], 0, ',', ' '); ?> DA</div>
                        <button class="btn btn-primary" data-order data-id="<?= (int)$product['id']; ?>" data-name="<?= sanitize($product['name']); ?>">
                            Commander
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<div class="modal" id="order-modal" aria-hidden="true">
    <div class="modal-content">
        <button class="close-btn" id="modal-close" aria-label="Fermer la fenêtre">×</button>
        <h3>Finaliser la réservation</h3>
        <p style="color: var(--muted);">Produit sélectionné : <strong id="product-label"></strong></p>
        <div id="order-status"></div>
        <form id="order-form" method="post" action="order-handler.php">
            <input type="hidden" name="product_id">
            <label>
                Nom complet
                <input type="text" name="client_name" required placeholder="Ex. Lina Bensalem">
            </label>
            <label>
                Téléphone
                <input type="tel" name="client_phone" required placeholder="+213 5 55 55 55 55">
            </label>
            <label>
                Adresse complète
                <textarea name="client_address" rows="3" required placeholder="Numéro, rue, commune, wilaya"></textarea>
            </label>
            <label>
                Email
                <input type="email" name="client_email" placeholder="vous@email.fr">
            </label>
            <label>
                Quantité
                <input type="number" min="1" step="1" name="qty" value="1" required>
            </label>
            <button class="btn btn-primary" type="submit">Envoyer la commande</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

