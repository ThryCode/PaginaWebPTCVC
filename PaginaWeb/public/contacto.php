<?php include 'includes/header.php'; ?>

        <section class="page-header">
            <div class="container">
                <h2>Contáctanos</h2>
                <p>Estamos aquí para ayudarte</p>
            </div>
        </section>

        <section class="contact-section" id="contacto">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info animate-fade-left">
                        <h2>Ponte en Contacto</h2>
                        <p>No dudes en comunicarte. Simplemente complete el formulario de contacto y nos aseguraremos de responderle lo más rápido posible.</p>
                        <div class="contact-items">
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                </div>
                                <div>
                                    <h4>Visita nuestra oficina</h4>
                                    <p>Carretera a Planta Mecánica, No. 39 B</p>
                                    <p>Santa Clara, Villa Clara, Cuba</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                </div>
                                <div>
                                    <h4>Correos</h4>
                                    <p>pctvillaclara@pctvc.cu</p>
                                    <p>clientes@pctvc.cu</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                </div>
                                <div>
                                    <h4>Teléfono Fijo</h4>
                                    <p>+53 42281551</p>
                                    <p>Extensiones: 101-107</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </div>
                                <div>
                                    <h4>Horario</h4>
                                    <p>Lunes - Jueves: 8:00 AM - 5:00 PM</p>
                                    <p>Viernes: 8:00 AM - 4:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form-wrap animate-fade-right">
                        <form class="contact-form" id="contactForm" onsubmit="return handleSubmit(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" required placeholder="Nombre">
                                </div>
                                <div class="form-group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" id="apellidos" name="apellidos" placeholder="Apellidos">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="correo">Correo</label>
                                <input type="email" id="correo" name="correo" required placeholder="Dirección de Correo">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" placeholder="+53 555 12345">
                            </div>
                            <div class="form-group">
                                <label for="asunto">Asunto</label>
                                <input type="text" id="asunto" name="asunto" required placeholder="Asunto">
                            </div>
                            <div class="form-group">
                                <label for="mensaje">Mensaje</label>
                                <textarea id="mensaje" name="mensaje" rows="5" required placeholder="Mensaje"></textarea>
                            </div>
                            <div id="formMessage" class="form-message"></div>
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-primary" style="width:100%">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-right:8px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
