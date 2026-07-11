<div
    class="commerce-tab-panel {{ ($activeTab ?? 'usuarios') === 'finanzas' ? 'is-active' : '' }}"
    data-commerce-tab-panel="finanzas"
>
    <section
        class="commerce-finance"
        data-finance-root
        data-endpoint="{{ route('comercio.finance.data') }}"
        data-export-url="{{ route('comercio.finance.movements.export') }}"
        data-bank-primary-url="{{ url('/comercio/finanzas/datos-bancarios/__ID__/principal') }}"
        data-bank-toggle-url="{{ url('/comercio/finanzas/datos-bancarios/__ID__/estado') }}"
        data-active-subtab="{{ session('finance_tab', 'movements') }}"
    >
        <header class="commerce-finance__header">
            <div>
                <h2>Gestión financiera</h2>
                <p>Ventas, pagos, facturación, certificados, cuentas bancarias y aclaraciones.</p>
            </div>
            <button type="button" class="commerce-finance__button commerce-finance__button--soft" data-finance-refresh>Actualizar</button>
        </header>

        <nav class="commerce-finance__tabs" aria-label="Secciones financieras">
            <button type="button" data-finance-tab="movements">Movimientos</button>
            <button type="button" data-finance-tab="settlements">Pagos</button>
            <button type="button" data-finance-tab="invoices">Facturación</button>
            <button type="button" data-finance-tab="tax">Datos fiscales</button>
            <button type="button" data-finance-tab="bank">Datos bancarios</button>
            <button type="button" data-finance-tab="disputes">Aclaraciones</button>
        </nav>

        <div class="commerce-finance__kpis">
            @foreach ([
                ['gross_sales', 'Ventas brutas'],
                ['commission', 'Comisión Petpay'],
                ['available', 'Saldo disponible'],
                ['pending', 'Saldo pendiente'],
                ['held', 'Saldo retenido'],
                ['paid', 'Liquidado'],
            ] as [$key, $label])
                <article class="commerce-finance__kpi">
                    <span>{{ $label }}</span>
                    <strong data-finance-kpi="{{ $key }}">$0.00</strong>
                </article>
            @endforeach
        </div>

        <section class="commerce-finance__panel" data-finance-panel="movements">
            <div class="commerce-finance__filters">
                <label><span>Desde</span><input type="date" data-finance-filter="date_from"></label>
                <label><span>Hasta</span><input type="date" data-finance-filter="date_to"></label>
                <label><span>Sucursal</span><select data-finance-filter="branch_id"><option value="">Todas</option></select></label>
                <label>
                    <span>Tipo</span>
                    <select data-finance-filter="type">
                        <option value="">Todos</option>
                        <option value="sale">Venta</option>
                        <option value="commission">Comisión</option>
                        <option value="refund">Reembolso</option>
                        <option value="chargeback">Contracargo</option>
                        <option value="adjustment">Ajuste</option>
                        <option value="settlement">Liquidación</option>
                    </select>
                </label>
                <label>
                    <span>Estatus</span>
                    <select data-finance-filter="status">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="processing">En proceso</option>
                        <option value="available">Disponible</option>
                        <option value="held">Retenido</option>
                        <option value="paid">Pagado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </label>
                <label class="commerce-finance__filter-search"><span>Buscar</span><input type="search" data-finance-filter="search" placeholder="Pedido, concepto o UUID"></label>
                <button type="button" class="commerce-finance__button" data-finance-apply>Aplicar</button>
                <button type="button" class="commerce-finance__button commerce-finance__button--soft" data-finance-export>Excel</button>
            </div>

            <div class="commerce-finance__table-wrap">
                <table class="commerce-finance__table">
                    <thead><tr><th>Fecha</th><th>Concepto</th><th>Sucursal</th><th>Bruto</th><th>Comisión</th><th>Envío</th><th>Neto</th><th>Estatus</th></tr></thead>
                    <tbody data-finance-movements><tr><td colspan="8" class="commerce-finance__empty">Cargando movimientos...</td></tr></tbody>
                </table>
            </div>
        </section>

        <section class="commerce-finance__panel" data-finance-panel="settlements" hidden>
            <div class="commerce-finance__section-head"><div><h3>Pagos y liquidaciones</h3><p>Detalle semanal, comisiones, retenciones, ajustes y comprobantes.</p></div></div>
            <div class="commerce-finance__cards" data-finance-settlements></div>
        </section>

        <section class="commerce-finance__panel" data-finance-panel="invoices" hidden>
            <div class="commerce-finance__section-head"><div><h3>Facturación</h3><p>CFDI emitidos, series, folios y ambientes.</p></div></div>

            <div class="commerce-finance__table-wrap">
                <table class="commerce-finance__table">
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Serie/Folio</th><th>UUID</th><th>Total</th><th>Estatus</th></tr></thead>
                    <tbody data-finance-invoices></tbody>
                </table>
            </div>

            <div class="commerce-finance__split">
                <div class="commerce-finance__cards" data-finance-series></div>

                <form class="commerce-finance__form" method="POST" action="{{ route('comercio.finance.series.store') }}">
                    @csrf
                    <div class="commerce-finance__section-head"><div><h3>Nueva serie y folio</h3><p>Configura una serie por tipo de CFDI, sucursal y ambiente.</p></div></div>

                    <div class="commerce-finance__form-grid">
                        <label><span>Serie</span><input name="series" maxlength="20" placeholder="A" required></label>
                        <label>
                            <span>Tipo CFDI</span>
                            <select name="cfdi_type" required>
                                <option value="I">Ingreso</option>
                                <option value="E">Egreso</option>
                                <option value="P">Pago</option>
                                <option value="T">Traslado</option>
                                <option value="N">Nómina</option>
                            </select>
                        </label>
                        <label><span>Folio inicial</span><input type="number" name="initial_folio" min="1" value="1" required></label>
                        <label><span>Folio actual</span><input type="number" name="current_folio" min="1" value="1"></label>
                        <label><span>Sucursal</span><select name="branch_id" data-finance-branch-form><option value="">General</option></select></label>
                        <label>
                            <span>Ambiente</span>
                            <select name="environment" required>
                                <option value="sandbox">Pruebas</option>
                                <option value="production">Producción</option>
                            </select>
                        </label>
                        <label class="commerce-finance__check"><input type="checkbox" name="is_default" value="1"><span>Serie predeterminada</span></label>
                        <label class="commerce-finance__check"><input type="checkbox" name="is_active" value="1" checked><span>Activa</span></label>
                    </div>

                    <div class="commerce-finance__actions"><button class="commerce-finance__button" type="submit">Guardar serie</button></div>
                </form>
            </div>
        </section>

        <section class="commerce-finance__panel" data-finance-panel="tax" hidden>
            <form class="commerce-finance__form" method="POST" action="{{ route('comercio.finance.tax.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="commerce-finance__section-head">
                    <div><h3>Datos fiscales y certificados</h3><p>Los archivos se almacenan de forma privada y las contraseñas se cifran.</p></div>
                    <span class="commerce-finance__status" data-finance-tax-status>Incompleto</span>
                </div>

                <div class="commerce-finance__form-grid">
                    <label>
                        <span>Tipo de persona</span>
                        <select name="person_type" required>
                            <option value="moral">Persona moral</option>
                            <option value="fisica">Persona física</option>
                        </select>
                    </label>
                    <label><span>RFC</span><input name="rfc" maxlength="13" required></label>
                    <label class="is-wide"><span>Razón social / nombre</span><input name="legal_name" required></label>
                    <label>
                        <span>Régimen fiscal</span>
                        <select name="tax_regime" required>
                            <option value="">Selecciona</option>
                            <option value="601">601 - General de Ley Personas Morales</option>
                            <option value="603">603 - Personas Morales con Fines no Lucrativos</option>
                            <option value="605">605 - Sueldos y Salarios</option>
                            <option value="606">606 - Arrendamiento</option>
                            <option value="612">612 - Personas Físicas con Actividades Empresariales y Profesionales</option>
                            <option value="616">616 - Sin obligaciones fiscales</option>
                            <option value="621">621 - Incorporación Fiscal</option>
                            <option value="625">625 - Actividades Empresariales con ingresos por Plataformas Tecnológicas</option>
                            <option value="626">626 - Régimen Simplificado de Confianza</option>
                        </select>
                    </label>
                    <label><span>Código postal fiscal</span><input name="postal_code" maxlength="10" required></label>
                    <label>
                        <span>Uso CFDI predeterminado</span>
                        <select name="cfdi_use">
                            <option value="">Selecciona</option>
                            <option value="G01">G01 - Adquisición de mercancías</option>
                            <option value="G03">G03 - Gastos en general</option>
                            <option value="I08">I08 - Otra maquinaria y equipo</option>
                            <option value="P01">P01 - Por definir</option>
                            <option value="S01">S01 - Sin efectos fiscales</option>
                            <option value="CP01">CP01 - Pagos</option>
                            <option value="CN01">CN01 - Nómina</option>
                        </select>
                    </label>
                    <label><span>Correo fiscal</span><input type="email" name="tax_email" required></label>
                    <label><span>Calle</span><input name="fiscal_street"></label>
                    <label><span>Número</span><input name="fiscal_number"></label>
                    <label><span>Colonia</span><input name="fiscal_colony"></label>
                    <label><span>Municipio / ciudad</span><input name="fiscal_city"></label>
                    <label><span>Estado</span><input name="fiscal_state"></label>
                    <label>
                        <span>Ambiente de facturación</span>
                        <select name="environment" required>
                            <option value="sandbox">Pruebas</option>
                            <option value="production">Producción</option>
                        </select>
                    </label>
                </div>

                <div class="commerce-finance__document-grid">
                    <label><span>Constancia de situación fiscal</span><input type="file" name="csf" accept=".pdf"></label>
                    <label><span>Opinión de cumplimiento</span><input type="file" name="compliance_opinion" accept=".pdf"></label>
                    <label><span>e.firma .cer</span><input type="file" name="efirma_cer" accept=".cer"></label>
                    <label><span>e.firma .key</span><input type="file" name="efirma_key" accept=".key"></label>
                    <label><span>Contraseña e.firma</span><input type="password" name="efirma_password" autocomplete="new-password"></label>
                    <label><span>CSD .cer</span><input type="file" name="csd_cer" accept=".cer"></label>
                    <label><span>CSD .key</span><input type="file" name="csd_key" accept=".key"></label>
                    <label><span>Contraseña CSD</span><input type="password" name="csd_password" autocomplete="new-password"></label>
                </div>

                <div class="commerce-finance__actions"><button class="commerce-finance__button" type="submit">Guardar datos fiscales</button></div>
            </form>
        </section>

        <section class="commerce-finance__panel" data-finance-panel="bank" hidden>
            <div class="commerce-finance__section-head"><div><h3>Datos bancarios</h3><p>Registra varias cuentas y define cuál será la principal.</p></div></div>
            <div class="commerce-finance__cards" data-finance-bank-accounts></div>

            <form class="commerce-finance__form" method="POST" action="{{ route('comercio.finance.bank.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="commerce-finance__form-grid">
                    <label>
                        <span>Banco</span>
                        <select name="bank_code" data-bank-select required>
                            <option value="">Selecciona</option>
                            <option value="002" data-name="BANAMEX">002 - BANAMEX</option>
                            <option value="012" data-name="BBVA MÉXICO">012 - BBVA MÉXICO</option>
                            <option value="014" data-name="SANTANDER">014 - SANTANDER</option>
                            <option value="021" data-name="HSBC">021 - HSBC</option>
                            <option value="036" data-name="INBURSA">036 - INBURSA</option>
                            <option value="044" data-name="SCOTIABANK">044 - SCOTIABANK</option>
                            <option value="058" data-name="BANREGIO">058 - BANREGIO</option>
                            <option value="072" data-name="BANORTE">072 - BANORTE</option>
                            <option value="127" data-name="AZTECA">127 - BANCO AZTECA</option>
                            <option value="137" data-name="BANCOPPEL">137 - BANCOPPEL</option>
                            <option value="646" data-name="STP">646 - STP</option>
                            <option value="906" data-name="KLAR">906 - KLAR</option>
                        </select>
                        <input type="hidden" name="bank_name" data-bank-name>
                    </label>
                    <label class="is-wide"><span>Titular</span><input name="account_holder" required></label>
                    <label><span>RFC del titular</span><input name="holder_rfc" maxlength="13"></label>
                    <label><span>CLABE</span><input name="clabe" inputmode="numeric" minlength="18" maxlength="18" required></label>
                    <label><span>Número de cuenta</span><input name="account_number" maxlength="30"></label>
                    <label><span>Últimos 4 de tarjeta</span><input name="card_last4" maxlength="4" inputmode="numeric"></label>
                    <label><span>Sucursal bancaria</span><input name="bank_branch"></label>
                    <label><span>Convenio / referencia</span><input name="agreement_reference"></label>
                    <label>
                        <span>Moneda</span>
                        <select name="currency" required><option value="MXN">MXN</option><option value="USD">USD</option></select>
                    </label>
                    <label class="commerce-finance__check"><input type="checkbox" name="is_primary" value="1"><span>Usar como cuenta principal</span></label>
                    <label class="is-wide"><span>Comprobante bancario</span><input type="file" name="proof" accept=".pdf,.png,.jpg,.jpeg,.webp"></label>
                    <label class="is-wide"><span>Estado de cuenta</span><input type="file" name="statement" accept=".pdf"></label>
                </div>

                <div class="commerce-finance__actions"><button class="commerce-finance__button" type="submit">Registrar cuenta bancaria</button></div>
            </form>
        </section>

        <section class="commerce-finance__panel" data-finance-panel="disputes" hidden>
            <div class="commerce-finance__section-head"><div><h3>Aclaraciones</h3><p>Seguimiento por folio, prioridad, fecha límite y estado.</p></div></div>
            <div class="commerce-finance__cards" data-finance-disputes></div>

            <form class="commerce-finance__form" method="POST" action="{{ route('comercio.finance.disputes.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="commerce-finance__form-grid">
                    <label>
                        <span>Tipo</span>
                        <select name="type" required>
                            <option value="payment">Pago</option>
                            <option value="settlement">Liquidación</option>
                            <option value="commission">Comisión</option>
                            <option value="refund">Reembolso</option>
                            <option value="chargeback">Contracargo</option>
                            <option value="invoice">Factura</option>
                            <option value="other">Otro</option>
                        </select>
                    </label>
                    <label>
                        <span>Prioridad</span>
                        <select name="priority" required>
                            <option value="normal">Normal</option>
                            <option value="low">Baja</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </label>
                    <label><span>Monto reclamado</span><input type="number" name="claimed_amount" min="0" step="0.01"></label>
                    <label class="is-wide"><span>Asunto</span><input name="subject" required></label>
                    <label><span>ID pedido</span><input type="number" name="order_id" min="1"></label>
                    <label><span>ID pago</span><input type="number" name="payment_transaction_id" min="1"></label>
                    <label><span>ID liquidación</span><input type="number" name="settlement_id" min="1"></label>
                    <label><span>Sucursal</span><select name="branch_id" data-finance-branch-form><option value="">General</option></select></label>
                    <label class="is-full"><span>Descripción</span><textarea name="description" rows="5" required></textarea></label>
                    <label class="is-full"><span>Evidencias</span><input type="file" name="attachments[]" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.xlsx,.csv"></label>
                </div>

                <div class="commerce-finance__actions"><button class="commerce-finance__button" type="submit">Registrar aclaración</button></div>
            </form>
        </section>
    </section>
</div>
