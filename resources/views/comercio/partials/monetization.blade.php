@php
    $campaignsCollection = collect($monetizationCampaigns ?? []);
    $campaignActive = $campaignsCollection->where('status', 'active')->count();
    $campaignBudget = $campaignsCollection->sum(fn ($item) => (float) $item->budget);
    $campaignSpent = $campaignsCollection->sum(fn ($item) => (float) $item->spent);
    $campaignSales = $campaignsCollection->sum(fn ($item) => (float) $item->attributed_sales);
    $campaignOrders = $campaignsCollection->sum(fn ($item) => (int) $item->orders);
    $campaignClicks = $campaignsCollection->sum(fn ($item) => (int) $item->clicks);
    $campaignImpressions = $campaignsCollection->sum(fn ($item) => (int) $item->impressions);
    $campaignRoi = $campaignSpent > 0 ? (($campaignSales - $campaignSpent) / $campaignSpent) * 100 : 0;
@endphp

<div class="commerce-tab-panel {{ ($activeTab ?? 'usuarios') === 'monetizar' ? 'is-active' : '' }}" data-commerce-tab-panel="monetizar">
    <section class="growth-shell">
        <div class="growth-titlebar">
            <div>
                <h2>Monetizar</h2>
                <p>Promociones, cupones y campañas para aumentar ventas y recurrencia.</p>
            </div>
            <button type="button" class="growth-primary-button" data-monetize-open-form>Crear campaña</button>
        </div>

        <div class="growth-kpi-grid">
            <article><span>Campañas activas</span><strong>{{ $campaignActive }}</strong><small>{{ $campaignsCollection->count() }} registradas</small></article>
            <article><span>Presupuesto</span><strong>${{ number_format($campaignBudget, 2) }}</strong><small>${{ number_format($campaignSpent, 2) }} utilizado</small></article>
            <article><span>Ventas atribuidas</span><strong>${{ number_format($campaignSales, 2) }}</strong><small>{{ number_format($campaignRoi, 1) }}% ROI</small></article>
            <article><span>Conversiones</span><strong>{{ $campaignOrders }}</strong><small>{{ $campaignClicks }} clics</small></article>
        </div>

        <div class="growth-feature-grid">
            @foreach ([
                ['discount', 'Promociones', 'Descuentos por producto, categoría o sucursal.'],
                ['coupon', 'Cupones', 'Códigos con límite de uso y compra mínima.'],
                ['sponsored', 'Patrocinados', 'Mayor visibilidad dentro del marketplace.'],
                ['cashback', 'Cashback', 'Recompensas para impulsar recompra.'],
                ['referral', 'Referidos', 'Beneficios por recomendar nuevos clientes.'],
                ['membership', 'Membresías', 'Beneficios recurrentes y exclusivos.'],
            ] as [$type, $title, $description])
                <button type="button" class="growth-feature-card" data-monetize-preset="{{ $type }}">
                    <span class="growth-feature-icon">{{ strtoupper(substr($title, 0, 1)) }}</span>
                    <strong>{{ $title }}</strong>
                    <small>{{ $description }}</small>
                </button>
            @endforeach
        </div>

        <section class="growth-card">
            <div class="growth-filter-grid">
                <label><span>Buscar campaña</span><input type="search" data-monetize-search placeholder="Nombre, cupón o tipo"></label>
                <label><span>Estatus</span><select data-monetize-status><option value="">Todos</option><option value="draft">Borrador</option><option value="pending">Pendiente</option><option value="active">Activa</option><option value="paused">Pausada</option><option value="finished">Finalizada</option><option value="rejected">Rechazada</option></select></label>
                <label><span>Tipo</span><select data-monetize-type><option value="">Todos</option><option value="discount">Promoción</option><option value="coupon">Cupón</option><option value="sponsored">Patrocinado</option><option value="cashback">Cashback</option><option value="referral">Referidos</option><option value="membership">Membresía</option></select></label>
            </div>
        </section>

        <section class="growth-card">
            <div class="growth-card-heading">
                <div><h3>Campañas</h3><p>Seguimiento de rendimiento, vigencia y presupuesto.</p></div>
            </div>

            <div class="growth-campaign-list">
                @forelse ($campaignsCollection as $campaign)
                    <article class="growth-campaign-row" data-monetize-card data-search="{{ mb_strtolower($campaign->name.' '.$campaign->type.' '.$campaign->coupon_code, 'UTF-8') }}" data-status="{{ $campaign->status }}" data-type="{{ $campaign->type }}">
                        <div class="growth-campaign-main">
                            <span class="growth-status is-{{ $campaign->status }}">{{ str_replace('_', ' ', ucfirst($campaign->status)) }}</span>
                            <h4>{{ $campaign->name }}</h4>
                            <p>{{ ucfirst($campaign->type) }} · {{ $campaign->branch?->branch_name ?? 'Todas las sucursales' }}</p>
                            @if ($campaign->coupon_code)<code>{{ $campaign->coupon_code }}</code>@endif
                        </div>
                        <div class="growth-metric-strip">
                            <span><small>Impresiones</small><strong>{{ number_format($campaign->impressions) }}</strong></span>
                            <span><small>Clics</small><strong>{{ number_format($campaign->clicks) }}</strong></span>
                            <span><small>Pedidos</small><strong>{{ number_format($campaign->orders) }}</strong></span>
                            <span><small>Conversión</small><strong>{{ number_format($campaign->conversion_rate, 1) }}%</strong></span>
                            <span><small>Ventas</small><strong>${{ number_format((float) $campaign->attributed_sales, 2) }}</strong></span>
                            <span><small>ROI</small><strong>{{ number_format($campaign->roi, 1) }}%</strong></span>
                        </div>
                        <div class="growth-actions">
                            @if ($campaign->status === 'draft')
                                <form method="POST" action="{{ route('comercio.monetization.submit', $campaign) }}">@csrf<button type="submit">Enviar</button></form>
                            @endif
                            @if (in_array($campaign->status, ['active', 'paused'], true))
                                <form method="POST" action="{{ route('comercio.monetization.toggle', $campaign) }}">@csrf<button type="submit">{{ $campaign->status === 'active' ? 'Pausar' : 'Activar' }}</button></form>
                            @endif
                            <form method="POST" action="{{ route('comercio.monetization.destroy', $campaign) }}" onsubmit="return confirm('¿Eliminar campaña?');">@csrf @method('DELETE')<button type="submit">Eliminar</button></form>
                        </div>
                    </article>
                @empty
                    <div class="growth-empty">
                        <strong>Aún no hay campañas.</strong>
                        <span>Crea la primera para comenzar a medir ventas, clics y retorno.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </section>

    <div class="growth-modal" data-monetize-modal hidden>
        <div class="growth-modal-card">
            <div class="growth-modal-head">
                <div><h3>Nueva campaña</h3><p>Configura alcance, incentivo y vigencia.</p></div>
                <button type="button" data-monetize-close-form>×</button>
            </div>
            <form method="POST" action="{{ route('comercio.monetization.store') }}" class="growth-form">
                @csrf
                <div class="growth-form-grid">
                    <label class="wide"><span>Nombre</span><input type="text" name="name" required></label>
                    <label><span>Tipo</span><select name="type" data-monetize-type-field required><option value="discount">Promoción</option><option value="coupon">Cupón</option><option value="sponsored">Patrocinado</option><option value="cashback">Cashback</option><option value="referral">Referidos</option><option value="membership">Membresía</option></select></label>
                    <label><span>Alcance</span><select name="scope" required><option value="all">Todo el comercio</option><option value="branch">Sucursal</option><option value="category">Categoría</option><option value="product">Producto</option></select></label>
                    <label><span>Sucursal</span><select name="branch_id"><option value="">Todas</option>@foreach (collect($branches ?? []) as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>@endforeach</select></label>
                    <label><span>Presupuesto</span><input type="number" name="budget" min="0" step="0.01" value="0"></label>
                    <label><span>Tipo descuento</span><select name="discount_type"><option value="">No aplica</option><option value="percentage">Porcentaje</option><option value="fixed">Monto fijo</option></select></label>
                    <label><span>Valor</span><input type="number" name="discount_value" min="0" step="0.01"></label>
                    <label><span>Código cupón</span><input type="text" name="coupon_code"></label>
                    <label><span>Compra mínima</span><input type="number" name="minimum_purchase" min="0" step="0.01"></label>
                    <label><span>Límite usos</span><input type="number" name="usage_limit" min="1"></label>
                    <label><span>Cashback %</span><input type="number" name="cashback_percentage" min="0" max="100" step="0.01"></label>
                    <label><span>Inicio</span><input type="datetime-local" name="starts_at"></label>
                    <label><span>Fin</span><input type="datetime-local" name="ends_at"></label>
                    <label class="wide"><span>Productos</span><select name="product_ids[]" multiple size="5">@foreach (collect($catalogProducts ?? []) as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></label>
                    <label class="wide"><span>Categorías</span><select name="category_ids[]" multiple size="4">@foreach (collect($catalogCategories ?? []) as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
                    <label class="wide"><span>Descripción</span><textarea name="description"></textarea></label>
                </div>
                <div class="growth-modal-actions">
                    <button type="button" class="secondary" data-monetize-close-form>Cancelar</button>
                    <button type="submit" class="primary">Guardar campaña</button>
                </div>
            </form>
        </div>
    </div>
</div>