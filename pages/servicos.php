<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tracker.php';
track_visit('Serviços');
$pageTitle = 'Serviços – ' . CLINIC_NAME;
$activePage = 'servicos';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- PAGE HERO -->
<section class="page-hero">
    <h1><i class="fas fa-stethoscope"></i> Nossos Serviços</h1>
    <p>Tudo o que o seu pet precisa, com qualidade e carinho profissional.</p>
</section>

<!-- SERVIÇOS DETALHADOS -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Saúde & Bem-estar</span>
            <h2>Serviços Clínicos</h2>
        </div>
        <div class="services-grid">
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-stethoscope"></i></div>
                <h3>Clínica Geral</h3>
                <p>Consultas clínicas completas para diagnóstico e tratamento.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-syringe"></i></div>
                <h3>Vacinação</h3>
                <p>Calendário vacinal atualizado para cães, gatos e outros animais.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-flask"></i></div>
                <h3>Exames Laboratoriais</h3>
                <p>Hemograma, bioquímica, urinálise e parasitológico.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-x-ray"></i></div>
                <h3>Ultrassonografia</h3>
                <p>Diagnóstico por imagem para avaliação interna detalhada.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-cut"></i></div>
                <h3>Cirurgias</h3>
                <p>Castração, cirurgias eletivas e emergências cirúrgicas.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-teeth"></i></div>
                <h3>Odontologia</h3>
                <p>Limpeza dentária, extração e tratamento periodontal.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-heartbeat"></i></div>
                <h3>Cardiologia</h3>
                <p>Eletrocardiograma e avaliação cardiovascular especializada.</p>
            </a>
            <a href="/pages/consultas.php" class="service-card">
                <div class="service-icon"><i class="fas fa-allergies"></i></div>
                <h3>Dermatologia</h3>
                <p>Tratamento de alergias, dermatites e doenças de pele.</p>
            </a>
            <a href="https://wa.me/<?= CLINIC_WHATS ?>?text=Olá! Preciso de atendimento de urgência." target="_blank" class="service-card">
                <div class="service-icon" style="background:linear-gradient(135deg,#e74c3c,#c0392b);"><i class="fas fa-ambulance"></i></div>
                <h3>Urgência & Emergência</h3>
                <p>Atendimento emergencial com hora marcada nos finais de semana.</p>
            </a>
        </div>
    </div>
</section>

<!-- HORÁRIOS (mesma tabela do index) -->
<section class="section section-alt" id="horarios">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Agenda</span>
            <h2>Horários de Atendimento</h2>
            <p>Confira os horários disponíveis para cada tipo de serviço.</p>
        </div>
        <div class="schedules-grid">

            <div class="schedule-card">
                <div class="schedule-card-header">
                    <i class="fas fa-stethoscope"></i>
                    <h3>Consultas Veterinárias</h3>
                </div>
                <table class="schedule-table">
                    <thead><tr><th>Dia</th><th>Horário</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>Segunda-feira</td><td>08:00 – 18:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Terça-feira</td><td>08:00 – 18:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quarta-feira</td><td>08:00 – 18:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quinta-feira</td><td>08:00 – 18:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sexta-feira</td><td>08:00 – 17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sábado</td><td>08:00 – 13:00</td><td class="status-limit"><i class="fas fa-circle"></i> Limitado</td></tr>
                        <tr><td>Domingo</td><td>–</td><td class="status-closed"><i class="fas fa-circle"></i> Fechado</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="schedule-card">
                <div class="schedule-card-header">
                    <i class="fas fa-scissors"></i>
                    <h3>Banho & Tosa</h3>
                </div>
                <table class="schedule-table">
                    <thead><tr><th>Dia</th><th>Horário</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>Segunda-feira</td><td>08:00 – 17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Terça-feira</td><td>08:00 – 17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quarta-feira</td><td>08:00 – 17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Quinta-feira</td><td>08:00 – 17:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sexta-feira</td><td>08:00 – 16:00</td><td class="status-open"><i class="fas fa-circle"></i> Aberto</td></tr>
                        <tr><td>Sábado</td><td>08:00 – 14:00</td><td class="status-limit"><i class="fas fa-circle"></i> Limitado</td></tr>
                        <tr><td>Domingo</td><td>–</td><td class="status-closed"><i class="fas fa-circle"></i> Fechado</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="schedule-card">
                <div class="schedule-card-header" style="background:#e74c3c;">
                    <i class="fas fa-ambulance"></i>
                    <h3>Urgência</h3>
                </div>
                <table class="schedule-table">
                    <thead><tr><th>Período</th><th>Horário</th><th>Contato</th></tr></thead>
                    <tbody>
                        <tr><td>Seg – Sex</td><td>18:00 – 20:00</td><td><?= CLINIC_PHONE ?></td></tr>
                        <tr><td>Sábado</td><td>13:00 – 17:00</td><td><?= CLINIC_PHONE ?></td></tr>
                        <tr><td>Domingo</td><td>09:00 – 13:00</td><td><?= CLINIC_PHONE ?></td></tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>

<!-- PREÇOS -->
<section class="section" id="precos">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Transparência</span>
            <h2>Tabela de Preços</h2>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <div class="price-card-header">
                    <div class="category-icon"><i class="fas fa-stethoscope"></i></div>
                    <h3>Consultas & Vacinas</h3>
                </div>
                <table class="price-table">
                    <tr><td>Consulta Clínica Geral</td><td>R$ 120,00</td></tr>
                    <tr><td>Retorno (até 15 dias)</td><td>R$ 60,00</td></tr>
                    <tr><td>Consulta Domiciliar</td><td>R$ 200,00</td></tr>
                    <tr><td>Vacina Antirrábica</td><td>R$ 55,00</td></tr>
                    <tr><td>V8 / V10 (cão)</td><td>R$ 80,00</td></tr>
                    <tr><td>Vermifugação</td><td>R$ 40,00</td></tr>
                </table>
            </div>
            <div class="price-card featured">
                <div class="price-card-header">
                    <div class="category-icon"><i class="fas fa-scissors"></i></div>
                    <h3>Banho & Tosa</h3>
                </div>
                <table class="price-table">
                    <tr><td>Banho – Porte P (até 5kg)</td><td>R$ 50,00</td></tr>
                    <tr><td>Banho – Porte M (5–15kg)</td><td>R$ 70,00</td></tr>
                    <tr><td>Banho – Porte G (15–30kg)</td><td>R$ 90,00</td></tr>
                    <tr><td>Tosa Higiênica</td><td>R$ 40,00</td></tr>
                    <tr><td>Tosa Completa – P</td><td>R$ 80,00</td></tr>
                    <tr><td>Tosa Completa – G</td><td>R$ 130,00</td></tr>
                </table>
            </div>
            <div class="price-card">
                <div class="price-card-header">
                    <div class="category-icon"><i class="fas fa-flask"></i></div>
                    <h3>Exames & Procedimentos</h3>
                </div>
                <table class="price-table">
                    <tr><td>Hemograma Completo</td><td>R$ 85,00</td></tr>
                    <tr><td>Bioquímica (fígado/rim)</td><td>R$ 120,00</td></tr>
                    <tr><td>Ultrassonografia</td><td>R$ 180,00</td></tr>
                    <tr><td>Eletrocardiograma</td><td>R$ 150,00</td></tr>
                    <tr><td>Castração (cão/gata)</td><td>R$ 450,00</td></tr>
                    <tr><td>Limpeza Dentária</td><td>R$ 280,00</td></tr>
                </table>
            </div>
        </div>
    </div>
</section>

<a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" class="whatsapp-float">
    <i class="fab fa-whatsapp"></i>
</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
