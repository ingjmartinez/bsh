<div class="modal fade" id="modalEntrevista" tabindex="-1" aria-labelledby="modalEntrevistaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEntrevistaLabel">Nueva entrevista online</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formEntrevista" class="modal-body" autocomplete="off">
                <input type="hidden" name="id" id="entrevista_id">

                <div class="bg-light rounded p-3 mb-3">
                    <h6 class="text-primary fw-semibold mb-0">
                        <i class="ri-user-line align-bottom me-1"></i> Datos personales del candidato
                    </h6>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_completo" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Edad</label>
                        <input type="number" name="edad" class="form-control" min="0" max="120">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="50">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" maxlength="500">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado civil</label>
                        <select name="estado_civil" class="form-select">
                            <option value="">Seleccione</option>
                            <option value="Soltero/a">Soltero/a</option>
                            <option value="Casado/a">Casado/a</option>
                            <option value="Unión libre">Unión libre</option>
                            <option value="Divorciado/a">Divorciado/a</option>
                            <option value="Viudo/a">Viudo/a</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hijos</label>
                        <input type="number" name="hijos" class="form-control" min="0" max="50">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">¿Estudia actualmente?</label>
                        <input type="text" name="estudia_actualmente" class="form-control" maxlength="255" placeholder="Carrera y nivel, o No">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Licencia de conducir / Vehículo</label>
                        <input type="text" name="licencia_vehiculo" class="form-control" maxlength="255" placeholder="Tipo de licencia y vehículo">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">¿Está laborando actualmente?</label>
                        <input type="text" name="laborando_actualmente" class="form-control" maxlength="255" placeholder="Sí / No, dónde">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="bg-light rounded p-3 mb-3 mt-4">
                    <h6 class="text-primary fw-semibold mb-0">
                        <i class="ri-briefcase-line align-bottom me-1"></i> Experiencia laboral
                    </h6>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Último empleo y posición</label>
                        <input type="text" name="ultimo_empleo_posicion" class="form-control" maxlength="255">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tiempo</label>
                        <input type="text" name="tiempo" class="form-control" maxlength="100" placeholder="Ej. 2 años">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Salario (RD$)</label>
                        <input type="number" name="salario" class="form-control" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Fecha de salida y motivo</label>
                        <input type="text" name="fecha_salida_motivo" class="form-control" maxlength="500">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Comentarios</label>
                        <textarea name="comentarios" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha de la llamada</label>
                        <input type="date" name="fecha_llamada" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entrevistado por</label>
                        <input type="text" name="entrevistado_por" class="form-control" maxlength="150">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="bg-light rounded p-3 mb-3 mt-4">
                    <h6 class="text-primary fw-semibold mb-0">
                        <i class="ri-file-list-3-line align-bottom me-1"></i> Respecto a la vacante solicitada
                    </h6>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">¿A qué vacante aplica?</label>
                        <input type="text" name="vacante_aplica" class="form-control" maxlength="255" placeholder="Nombre de la vacante">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">¿Tiene experiencia demostrable en lo que aplica?</label>
                        <textarea name="experiencia_demostrable" class="form-control" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">¿Qué conoce del área?</label>
                        <textarea name="conoce_del_area" class="form-control" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">¿Cuáles son sus fortalezas?</label>
                        <textarea name="fortalezas" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">¿Cuáles son sus debilidades?</label>
                        <textarea name="debilidades" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Manejo de Excel (1 al 10)</label>
                        <select name="manejo_excel" class="form-select">
                            <option value="">Seleccione</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEntrevista" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

