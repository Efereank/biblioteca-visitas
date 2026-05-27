import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

// Config global de SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

function showSuccess(message) {
    Swal.fire({ icon: 'success', title: '¡Éxito!', text: message, confirmButtonColor: '#2563eb', confirmButtonText: 'Aceptar' });
}

function showError(message) {
    Swal.fire({ icon: 'error', title: 'Error', text: message, confirmButtonColor: '#2563eb', confirmButtonText: 'Aceptar' });
}

function showWarning(message) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: message, confirmButtonColor: '#2563eb', confirmButtonText: 'Aceptar' });
}

async function showConfirm(options = {}) {
    const result = await Swal.fire({
        title: options.title || '¿Está seguro?',
        text: options.text || 'Esta acción no se puede deshacer.',
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: options.confirmButtonColor || '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: options.confirmButtonText || 'Sí, continuar',
        cancelButtonText: options.cancelButtonText || 'Cancelar'
    });
    return result.isConfirmed;
}

function showToast(message, icon = 'success') {
    Toast.fire({ icon: icon, title: message });
}

window.wizard = function () {
    return {
        currentStep: 0,
        steps: [
            { title: 'Identificar', mobileTitle: 'Ident.' },
            { title: 'Datos', mobileTitle: 'Datos' },
            { title: 'Visita', mobileTitle: 'Visita' },
            { title: 'Confirmar', mobileTitle: 'Conf.' }
        ],
        form: {
            prefijo: 'V',
            numeroCedula: '',
            cedula: '',
            tipo_documento: 'C.I.',
            nombres: '',
            apellidos: '',
            email: '',
            telefono: '',
            genero: '',
            fecha_nacimiento: '',
            nacionalidad: '',
            direccion: '',
            municipio: '',
            parroquia: '',
            ciudad: '',
            codigo_postal: '',
            grado_instruccion: '',
            profesion: '',
            situacion_laboral: '',
            institucion_educativa_laboral: '',
            perfil_interes: '',
            subcategoria_interes: '',
            formato_preferido: '',
            idiomas_interes: [],
            discapacidad: 'Ninguna',
            necesidades_especiales: '',
            consentimiento_comunicacion: false,
            observaciones: '',
            institucion: '',
            tipo_visitante_id: '',
            proposito_id: '',
            sala_id: '',
            actividades_ids: [],
            representante_nombre: '',
            representante_cedula: '',
            representante_parentesco: ''
        },
        visitanteEncontrado: false,
        visitante: null,
        mensajeCedula: '',
        errorCedula: '',
        errorMenor: '',
        // Búsqueda del representante (Padre/Madre/Tutor/Otro)
        cedulaRepresentante: '',
        representanteEncontrado: false,
        representante: null,
        mensajeRepresentante: '',
        errorRepresentante: '',
        buscandoRepresentante: false,
        // Búsqueda del docente
        cedulaDocente: '',
        docente: null,
        docenteEncontrado: false,
        errorDocente: '',
        buscandoDocente: false,
        // Resto
        tiposVisitante: [],
        propositos: [],
        actividades: [],
        salas: [],
        municipios: [],
        parroquias: [],
        ciudades: [],
        perfilesInteres: [],
        subcategorias: {},
        edad: '',
        verificandoVisitaActiva: false,
        cedulaVerificada: false,
        cedulaDisponible: false,
        inicializado: false,
        role: 'recepcionista',
        storeUrl: '',
        historialUrl: '',

        get stepsVisibles() {
            const allSteps = [
                { title: 'Identificar', mobileTitle: 'Ident.' },
                { title: 'Datos', mobileTitle: 'Datos' },
                { title: 'Visita', mobileTitle: 'Visita' },
                { title: 'Confirmar', mobileTitle: 'Conf.' }
            ];
            if (this.role === 'recepcionista') return allSteps.filter(s => s.title !== 'Visita');
            if (this.role === 'bibliotecario') return allSteps.filter(s => s.title !== 'Datos');
            return allSteps;
        },
        get isPasoVisita() {
            return (this.role === 'admin' && this.currentStep === 2) ||
                   (this.role === 'bibliotecario' && this.currentStep === 1);
        },
        get cedulaCompleta() {
            if (this.form.tipo_documento === 'Sin Identificación') return '';
            if (!this.form.numeroCedula) return '';
            return this.form.prefijo + this.form.numeroCedula;
        },

        onTipoDocumentoChange() {
            this.visitanteEncontrado = false;
            this.visitante = null;
            this.mensajeCedula = '';
            this.errorCedula = '';
            this.cedulaVerificada = false;
            this.cedulaDisponible = false;
            this.errorMenor = '';
            if (this.form.tipo_documento === 'Sin Identificación') {
                this.form.numeroCedula = '';
                this.form.prefijo = 'V';
            }
        },

        onParentescoChange() {
            this.form.representante_nombre = '';
            this.form.representante_cedula = '';
            this.cedulaRepresentante = '';
            this.representanteEncontrado = false;
            this.representante = null;
            this.mensajeRepresentante = '';
            this.errorRepresentante = '';
            if (this.form.representante_parentesco !== 'Docente') {
                this.cedulaDocente = '';
                this.docente = null;
                this.docenteEncontrado = false;
                this.errorDocente = '';
            }
            this.errorMenor = '';
        },

        async buscarRepresentante() {
            const cedula = this.cedulaRepresentante.replace(/[^0-9]/g, '');
            if (!cedula || cedula.length < 7) {
                this.errorRepresentante = 'Ingrese una cédula válida de 7-8 dígitos';
                return;
            }
            this.buscandoRepresentante = true;
            this.errorRepresentante = '';
            this.representanteEncontrado = false;
            this.representante = null;
            this.mensajeRepresentante = '';
            this.representanteTieneVisitaActiva = false;
            try {
                const res = await fetch(`/api/visitantes/cedula/${cedula}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.tipo_documento === 'Sin Identificación') {
                        this.errorRepresentante = 'La cédula pertenece a un menor sin identificación.';
                        return;
                    }
                    this.representante = data;
                    this.representanteEncontrado = true;
                    this.form.representante_nombre = data.nombres + ' ' + data.apellidos;
                    this.form.representante_cedula = data.cedula;

                    // Verificar visita activa
                    const resActiva = await fetch(`/api/visitantes/${data.id}/visita-activa`);
                    const activaData = await resActiva.json();
                    if (activaData.tieneVisitaActiva) {
                        this.representanteTieneVisitaActiva = true;
                        this.mensajeRepresentante = 'Representante encontrado';
                    } else {
                        this.representanteTieneVisitaActiva = false;
                        this.mensajeRepresentante = 'Representante encontrado, pero no tiene visita activa. Debe registrar su entrada primero.';
                    }
                } else if (res.status === 404) {
                    this.representanteEncontrado = false;
                    this.mensajeRepresentante = 'No registrado. Puede ingresar los datos manualmente.';
                } else {
                    throw new Error('Error del servidor');
                }
            } catch (e) {
                this.errorRepresentante = 'Error al buscar. Intente nuevamente.';
            } finally {
                this.buscandoRepresentante = false;
            }
        },

        async buscarDocente() {
            const cedula = this.cedulaDocente.replace(/[^0-9]/g, '');
            if (!cedula || cedula.length < 7) {
                this.errorDocente = 'Ingrese una cédula válida de 7-8 dígitos';
                return;
            }
            this.buscandoDocente = true;
            this.errorDocente = '';
            this.docenteEncontrado = false;
            this.docente = null;
            this.docenteTieneVisitaActiva = false;
            try {
                const res = await fetch(`/api/visitantes/cedula/${cedula}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.tipo_documento === 'Sin Identificación') {
                        this.errorDocente = 'La cédula pertenece a un menor sin identificación.';
                        return;
                    }
                    this.docente = data;
                    this.docenteEncontrado = true;
                    this.form.representante_nombre = data.nombres + ' ' + data.apellidos;
                    this.form.representante_cedula = data.cedula;

                    const resActiva = await fetch(`/api/visitantes/${data.id}/visita-activa`);
                    const activaData = await resActiva.json();
                    if (activaData.tieneVisitaActiva) {
                        this.docenteTieneVisitaActiva = true;
                        this.errorDocente = '';
                    } else {
                        this.docenteTieneVisitaActiva = false;
                        this.errorDocente = 'Docente encontrado, pero no tiene visita activa. Debe registrar su entrada primero.';
                    }
                } else if (res.status === 404) {
                    this.docenteEncontrado = false;
                    this.errorDocente = 'Docente no encontrado. Regístrelo primero o ingrese los datos manualmente.';
                } else {
                    throw new Error('Error del servidor');
                }
            } catch (e) {
                this.errorDocente = 'Error al buscar docente. Intente nuevamente.';
            } finally {
                this.buscandoDocente = false;
            }
        },

        registrarMenor() {
            this.errorMenor = '';
            const parentesco = this.form.representante_parentesco;
            if (!parentesco) {
                this.errorMenor = 'Seleccione el parentesco del representante.';
                return;
            }
            if (parentesco === 'Docente') {
                if (!this.docenteEncontrado || !this.docente) {
                    this.errorMenor = 'Debe buscar y seleccionar un docente válido.';
                    return;
                }
                if (!this.docenteTieneVisitaActiva) {
                    this.errorMenor = 'El docente no tiene una visita activa. Debe registrar su entrada primero.';
                    return;
                }
                this.form.representante_nombre = this.docente.nombres + ' ' + this.docente.apellidos;
                this.form.representante_cedula = this.docente.cedula;
            } else {
                if (!this.form.representante_nombre.trim()) {
                    this.errorMenor = 'El nombre del representante es obligatorio.';
                    return;
                }
                if (!this.form.representante_cedula.trim()) {
                    this.errorMenor = 'La cédula del representante es obligatoria.';
                    return;
                }
                // Si se buscó y se encontró al representante, validar visita activa
                if (this.representanteEncontrado && this.representante) {
                    if (!this.representanteTieneVisitaActiva) {
                        this.errorMenor = 'El representante no tiene una visita activa. Debe registrar su entrada primero.';
                        return;
                    }
                    this.form.representante_nombre = this.representante.nombres + ' ' + this.representante.apellidos;
                    this.form.representante_cedula = this.representante.cedula;
                }
            }
            this.cedulaDisponible = true;
            this.cedulaVerificada = true;
            this.visitanteEncontrado = false;
            this.form.numeroCedula = '';
            this.form.cedula = '';
            this.mensajeCedula = '';
            this.errorCedula = '';
            this.currentStep = 1;
        },

        init(tipos, propositos, actividades, salas, visitantePrecargado, perfiles) {
            this.tiposVisitante = tipos;
            this.propositos = propositos;
            this.actividades = actividades;
            this.salas = salas || [];

            if (perfiles && perfiles.length) {
                this.perfilesInteres = perfiles.map(p => p.nombre);
                let subcatMap = {};
                perfiles.forEach(p => {
                    subcatMap[p.nombre] = p.subcategorias ? p.subcategorias.map(s => s.nombre) : [];
                });
                this.subcategorias = subcatMap;
            }

            if (this.role === 'bibliotecario' && this.salas.length === 1) {
                this.form.sala_id = this.salas[0].id;
            }

            if (visitantePrecargado) {
                const cedulaCompleta = visitantePrecargado.cedula;
                if (cedulaCompleta) {
                    const primerCaracter = cedulaCompleta.charAt(0);
                    if (['V', 'E'].includes(primerCaracter.toUpperCase())) {
                        this.form.prefijo = primerCaracter.toUpperCase();
                        this.form.numeroCedula = cedulaCompleta.substring(1);
                    } else {
                        this.form.prefijo = 'V';
                        this.form.numeroCedula = cedulaCompleta;
                    }
                }
                this.visitante = visitantePrecargado;
                this.visitanteEncontrado = true;
                this.cedulaVerificada = true;
                this.cedulaDisponible = false;
                this.mensajeCedula = 'Visitante encontrado';

                if (visitantePrecargado.fecha_nacimiento) {
                    const fecha = new Date(visitantePrecargado.fecha_nacimiento);
                    if (!isNaN(fecha.getTime())) {
                        const año = fecha.getFullYear();
                        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                        const dia = String(fecha.getDate()).padStart(2, '0');
                        this.form.fecha_nacimiento = `${año}-${mes}-${dia}`;
                    }
                }
            }

            this.inicializado = true;
        },

        calcularEdad() {
            if (this.form.fecha_nacimiento) {
                const hoy = new Date();
                const nacimiento = new Date(this.form.fecha_nacimiento);
                let edad = hoy.getFullYear() - nacimiento.getFullYear();
                const mes = hoy.getMonth() - nacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                    edad--;
                }
                this.edad = edad;
            } else {
                this.edad = '';
            }
        },

        getSalaNombre(id) {
            const sala = this.salas.find(s => s.id == id);
            return sala ? sala.nombre : '';
        },

        formatearCedula(event) {
            let valor = event.target.value;
            valor = valor.replace(/[^0-9]/g, '');
            if (valor.length > 8) valor = valor.slice(0, 8);
            this.form.numeroCedula = valor;
            this.validarCedulaLocal();
            this.cedulaVerificada = false;
            this.cedulaDisponible = false;
            this.visitanteEncontrado = false;
            this.visitante = null;
            this.mensajeCedula = '';
            this.errorCedula = '';
        },

        validarCedulaLocal() {
            const numero = this.form.numeroCedula;
            if (numero.length === 0) {
                this.errorCedula = '';
                return true;
            }
            if (numero.length < 7) {
                this.errorCedula = 'El número de cédula debe tener mínimo 7 dígitos';
                return false;
            }
            if (!/^\d+$/.test(numero)) {
                this.errorCedula = 'La cédula solo debe contener números';
                return false;
            }
            this.errorCedula = '';
            return true;
        },

        async buscarCedula() {
            if (this.form.tipo_documento === 'Sin Identificación') return;

            this.form.numeroCedula = this.form.numeroCedula.replace(/[^0-9]/g, '');

            if (!this.validarCedulaLocal()) {
                this.mensajeCedula = this.errorCedula;
                this.visitanteEncontrado = false;
                this.cedulaVerificada = false;
                this.cedulaDisponible = false;
                return;
            }

            if (this.form.numeroCedula.length < 7) {
                this.mensajeCedula = 'Ingrese su número de cédula completo';
                this.visitanteEncontrado = false;
                this.cedulaVerificada = false;
                this.cedulaDisponible = false;
                return;
            }

            const cedulaCompleta = this.cedulaCompleta;
            this.verificandoVisitaActiva = true;
            this.mensajeCedula = 'Verificando...';
            this.visitanteEncontrado = false;
            this.visitante = null;
            this.cedulaVerificada = false;
            this.cedulaDisponible = false;
            this.errorCedula = '';

            try {
                const res = await fetch(`/api/visitantes/cedula/${cedulaCompleta}`);

                if (!res.ok) {
                    if (res.status === 404) {
                        this.visitanteEncontrado = false;
                        this.visitante = null;
                        this.mensajeCedula = 'Cédula disponible para registro';
                        this.cedulaVerificada = true;
                        this.cedulaDisponible = true;
                    } else {
                        const errorData = await res.json().catch(() => ({}));
                        throw new Error(errorData.message || `Error ${res.status}`);
                    }
                } else {
                    const visitanteData = await res.json();

                    const resActiva = await fetch(`/api/visitantes/${visitanteData.id}/visita-activa`);
                    const dataActiva = await resActiva.json();

                    if (dataActiva.tieneVisitaActiva) {
                        if (this.role === 'bibliotecario' && !dataActiva.visita?.sala_id) {
                            this.visitante = visitanteData;
                            this.visitanteEncontrado = true;
                            this.mensajeCedula = 'Visitante encontrado';
                            this.cedulaVerificada = true;
                            this.cedulaDisponible = false;
                        } else {
                            this.visitanteEncontrado = false;
                            this.visitante = null;
                            this.mensajeCedula = '⚠️ Este visitante ya tiene una visita activa.';
                            this.errorCedula = 'El visitante tiene una visita activa';
                            this.cedulaVerificada = true;
                            this.cedulaDisponible = false;
                        }
                    } else {
                        if (this.role === 'bibliotecario') {
                            this.visitanteEncontrado = false;
                            this.visitante = null;
                            this.mensajeCedula = '⚠️ El visitante no ha registrado su entrada general. Debe pasar por recepción.';
                            this.errorCedula = 'Sin visita general activa';
                            this.cedulaVerificada = true;
                            this.cedulaDisponible = false;
                        } else {
                            this.visitante = visitanteData;
                            this.visitanteEncontrado = true;
                            this.mensajeCedula = 'Visitante encontrado';
                            this.cedulaVerificada = true;
                            this.cedulaDisponible = false;
                        }
                    }
                }
            } catch (e) {
                console.error(e);
                this.mensajeCedula = 'Error al verificar: ' + e.message;
                this.errorCedula = 'Error de conexión';
                this.cedulaVerificada = false;
                this.cedulaDisponible = false;
            } finally {
                this.verificandoVisitaActiva = false;
            }
        },

        async cargarParroquias(municipioId) {
            this.parroquias = [];
            this.ciudades = [];
            this.form.parroquia = '';
            this.form.ciudad = '';
            if (!municipioId) return;
            try {
                const res = await fetch(`/api/parroquias/${municipioId}`);
                this.parroquias = await res.json();
            } catch (e) { console.error(e); }
        },

        async cargarCiudades(municipioId) {
            this.ciudades = [];
            this.form.ciudad = '';
            if (!municipioId) return;
            try {
                const res = await fetch(`/api/ciudades/${municipioId}`);
                this.ciudades = await res.json();
            } catch (e) { console.error(e); }
        },

        async cargarUbicacion(municipioNombre) {
            const municipio = this.municipios.find(m => m.nombre === municipioNombre);
            const municipioId = municipio ? municipio.id : null;
            await this.cargarParroquias(municipioId);
            await this.cargarCiudades(municipioId);
        },

        async irAPasoDatos() {
            if (this.form.tipo_documento === 'Sin Identificación') {
                return;
            }
            if (!this.cedulaVerificada) {
                await this.buscarCedula();
            }

            if (!this.cedulaDisponible) {
                if (this.visitanteEncontrado) {
                    showWarning('Esta cédula ya está registrada. Use "Continuar con este visitante".');
                } else if (this.errorCedula) {
                    showError(this.errorCedula);
                } else {
                    showWarning('La cédula no está disponible.');
                }
                return;
            }

            if (!this.validarCedulaLocal()) {
                showError(this.errorCedula);
                return;
            }

            this.currentStep = 1;
        },

        async siguienteConVisitante() {
            if (!this.visitante) return;

            try {
                const res = await fetch(`/api/visitantes/${this.visitante.id}/visita-activa`);
                const data = await res.json();

                if (data.tieneVisitaActiva) {
                    if (this.role === 'bibliotecario' && !data.visita?.sala_id) {
                        // Permitir continuar
                    } else {
                        showWarning('Este visitante ya tiene una visita activa. Debe registrar la salida primero.');
                        this.visitanteEncontrado = false;
                        this.visitante = null;
                        this.mensajeCedula = '⚠️ El visitante tiene una visita activa';
                        this.errorCedula = 'El visitante tiene una visita activa';
                        return;
                    }
                } else {
                    if (this.role === 'bibliotecario') {
                        showWarning('El visitante no ha registrado su entrada general. Debe pasar primero por recepción.');
                        this.visitanteEncontrado = false;
                        this.visitante = null;
                        this.mensajeCedula = '⚠️ Sin visita general activa';
                        this.errorCedula = 'Sin visita general activa';
                        return;
                    }
                }

                this.form.nombres = this.visitante.nombres;
                this.form.apellidos = this.visitante.apellidos;
                this.form.email = this.visitante.email || '';
                this.form.telefono = this.visitante.telefono || '';
                this.form.genero = this.visitante.genero || '';
                if (this.visitante.fecha_nacimiento) {
                    const fecha = new Date(this.visitante.fecha_nacimiento);
                    if (!isNaN(fecha.getTime())) {
                        const año = fecha.getFullYear();
                        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                        const dia = String(fecha.getDate()).padStart(2, '0');
                        this.form.fecha_nacimiento = `${año}-${mes}-${dia}`;
                    } else {
                        this.form.fecha_nacimiento = '';
                    }
                } else {
                    this.form.fecha_nacimiento = '';
                }
                this.form.institucion = this.visitante.institucion || '';
                this.form.tipo_visitante_id = this.visitante.tipo_visitante_id;

                if (this.role === 'recepcionista' && !this.form.proposito_id && this.propositos.length > 0) {
                    this.form.proposito_id = this.propositos[0].id;
                }

                if (this.role === 'bibliotecario' && this.salas.length === 1) {
                    this.form.sala_id = this.salas[0].id;
                }

                if (this.role === 'recepcionista') {
                    this.currentStep = this.stepsVisibles.length - 1;
                } else if (this.role === 'bibliotecario') {
                    this.currentStep = 1;
                } else {
                    this.currentStep = 1;
                }
            } catch (e) {
                console.error(e);
                showError('Error al verificar visita activa');
            }
        },

        siguiente() {
            if (this.currentStep === 0) {
                this.currentStep = 1;
                return;
            }

            if (this.currentStep === 1) {
                if (this.role === 'recepcionista') {
                    this.currentStep = 2;
                    return;
                }
                if (this.role === 'bibliotecario') {
                    if (!this.form.proposito_id) {
                        showWarning('Seleccione un propósito');
                        return;
                    }
                    this.currentStep = 2;
                    return;
                }
                if (this.role === 'admin') {
                    this.currentStep = 2;
                    return;
                }
                return;
            }

            if (this.currentStep === 2) {
                if (this.role === 'admin' && !this.form.proposito_id) {
                    showWarning('Seleccione un propósito');
                    return;
                }
                this.currentStep = 3;
                return;
            }
        },

        async submitForm() {
            let payload = {};
            this.form.cedula = this.cedulaCompleta;

            if (this.visitanteEncontrado && this.visitante) {
                payload.visitante_id = this.visitante.id;
            } else {
                if (this.form.tipo_documento !== 'Sin Identificación') {
                    if (!this.validarCedulaLocal()) {
                        showError(this.errorCedula);
                        return;
                    }
                }

                payload.visitante_nuevo = {
                    tipo_documento: this.form.tipo_documento,
                    cedula: this.form.tipo_documento === 'Sin Identificación' ? '' : this.cedulaCompleta,
                    nombres: this.form.nombres,
                    apellidos: this.form.apellidos,
                    email: this.form.email || null,
                    telefono: this.form.telefono || null,
                    genero: this.form.genero || null,
                    fecha_nacimiento: this.form.fecha_nacimiento || null,
                    nacionalidad: this.form.nacionalidad || null,
                    direccion: this.form.direccion || null,
                    municipio: this.form.municipio || null,
                    parroquia: this.form.parroquia || null,
                    ciudad: this.form.ciudad || null,
                    codigo_postal: this.form.codigo_postal || null,
                    grado_instruccion: this.form.grado_instruccion || null,
                    profesion: this.form.profesion || null,
                    situacion_laboral: this.form.situacion_laboral || null,
                    institucion_educativa_laboral: this.form.institucion_educativa_laboral || null,
                    perfil_interes: this.form.perfil_interes || null,
                    subcategoria_interes: this.form.subcategoria_interes || null,
                    formato_preferido: this.form.formato_preferido || null,
                    idiomas_interes: this.form.idiomas_interes.length ? this.form.idiomas_interes : null,
                    discapacidad: this.form.discapacidad || null,
                    necesidades_especiales: this.form.necesidades_especiales || null,
                    consentimiento_comunicacion: this.form.consentimiento_comunicacion,
                    institucion: this.form.institucion || null,
                    tipo_visitante_id: this.form.tipo_visitante_id,
                    representante_nombre: this.form.tipo_documento === 'Sin Identificación' ? this.form.representante_nombre : null,
                    representante_cedula: this.form.tipo_documento === 'Sin Identificación' ? this.form.representante_cedula : null,
                    representante_parentesco: this.form.tipo_documento === 'Sin Identificación' ? this.form.representante_parentesco : null,
                    docente_id: (this.docente && this.docenteEncontrado) ? this.docente.id : null
                };
            }

            if (this.form.sala_id && this.form.perfil_interes) {
                if (this.visitanteEncontrado && this.visitante) {
                    payload.visitante_intereses = {
                        perfil_interes: this.form.perfil_interes,
                        subcategoria_interes: this.form.subcategoria_interes
                    };
                } else if (payload.visitante_nuevo) {
                    payload.visitante_nuevo.perfil_interes = this.form.perfil_interes || null;
                    payload.visitante_nuevo.subcategoria_interes = this.form.subcategoria_interes || null;
                }
            }

            payload.proposito_id = this.form.proposito_id;
            payload.sala_id = this.form.sala_id || null;
            payload.observaciones = this.form.observaciones || null;
            payload.actividades_ids = this.form.actividades_ids;

            Swal.fire({
                title: 'Registrando visita...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await fetch(this.storeUrl || '/visitas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    window.location.href = (this.historialUrl || '/historial') + '?mensaje=' + encodeURIComponent(data.message || 'Visita registrada exitosamente');
                } else {
                    Swal.close();
                    const error = await res.json();
                    if (error.errors) {
                        let mensaje = '';
                        for (let campo in error.errors) {
                            mensaje += error.errors[campo].join('\n') + '\n';
                        }
                        showError(mensaje);
                    } else {
                        showError('Error al registrar visita');
                    }
                }
            } catch (e) {
                Swal.close();
                console.error(e);
                showError('Error al registrar visita');
            }
        },

        getTipoNombre(id) {
            const tipo = this.tiposVisitante.find(t => t.id == id);
            return tipo ? tipo.nombre : '';
        },

        getPropositoNombre(id) {
            const p = this.propositos.find(p => p.id == id);
            return p ? p.nombre : '';
        },

        getActividadesNombres(ids) {
            if (!ids || ids.length === 0) return '';
            return ids.map(id => {
                const act = this.actividades.find(a => a.id == id);
                return act ? act.nombre : '';
            }).join(', ');
        }
    };
};


// ============================================
// FUNCIÓN VISITANTES MANAGER
// ============================================
window.visitantesManager = function() {
    return {
        modalAbierto: false,
        cargandoDetalle: false,
        visitanteSeleccionado: null,

        // Datos para edición
        municipios: [],
        parroquiasEdicion: [],
        ciudadesEdicion: [],
        modoEdicion: false,
        formEdicion: {},

        async init() {
            // Cargar municipios para los selects de edición
            try {
                const res = await fetch('/api/municipios');
                this.municipios = await res.json();
            } catch (e) { console.error(e); }
        },

        async verDetalle(id) {
            this.modalAbierto = true;
            this.cargandoDetalle = true;
            this.visitanteSeleccionado = null;
            this.modoEdicion = false;

            try {
                const response = await fetch(`/visitantes/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const visitante = await response.json();

                    const visitaActivaRes = await fetch(`/api/visitantes/${id}/visita-activa`);
                    const visitaActivaData = await visitaActivaRes.json();

                    this.visitanteSeleccionado = {
                        ...visitante,
                        visita_activa: visitaActivaData.tieneVisitaActiva ? visitaActivaData.visita : null
                    };
                } else {
                    showError('Error al cargar los detalles del visitante');
                    this.modalAbierto = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error al cargar los detalles');
                this.modalAbierto = false;
            } finally {
                this.cargandoDetalle = false;
            }
        },

        cerrarModal() {
            this.modalAbierto = false;
            this.visitanteSeleccionado = null;
            this.cargandoDetalle = false;
            this.modoEdicion = false;
        },

        abrirEdicion() {
            if (!this.visitanteSeleccionado) return;

            this.formEdicion = {
                id: this.visitanteSeleccionado.id,
                tipo_documento: this.visitanteSeleccionado.tipo_documento || 'C.I.',
                cedula: this.visitanteSeleccionado.cedula || '',
                nombres: this.visitanteSeleccionado.nombres || '',
                apellidos: this.visitanteSeleccionado.apellidos || '',
                email: this.visitanteSeleccionado.email || '',
                telefono: this.visitanteSeleccionado.telefono || '',
                genero: this.visitanteSeleccionado.genero || '',
                fecha_nacimiento: this.visitanteSeleccionado.fecha_nacimiento || '',
                nacionalidad: this.visitanteSeleccionado.nacionalidad || '',
                direccion: this.visitanteSeleccionado.direccion || '',
                municipio: this.visitanteSeleccionado.municipio || '',
                parroquia: this.visitanteSeleccionado.parroquia || '',
                ciudad: this.visitanteSeleccionado.ciudad || '',
                grado_instruccion: this.visitanteSeleccionado.grado_instruccion || '',
                profesion: this.visitanteSeleccionado.profesion || '',
                situacion_laboral: this.visitanteSeleccionado.situacion_laboral || '',
                institucion_educativa_laboral: this.visitanteSeleccionado.institucion_educativa_laboral || '',
                perfil_interes: this.visitanteSeleccionado.perfil_interes || '',
                subcategoria_interes: this.visitanteSeleccionado.subcategoria_interes || '',
                formato_preferido: this.visitanteSeleccionado.formato_preferido || '',
                discapacidad: this.visitanteSeleccionado.discapacidad || '',
                necesidades_especiales: this.visitanteSeleccionado.necesidades_especiales || '',
                consentimiento_comunicacion: this.visitanteSeleccionado.consentimiento_comunicacion || false,
                tipo_visitante_id: this.visitanteSeleccionado.tipo_visitante_id || '',
            };

            // Cargar parroquias y ciudades si hay municipio seleccionado
            if (this.formEdicion.municipio) {
                this.cargarUbicacionEdicion(this.formEdicion.municipio);
            }
            this.modoEdicion = true;
        },

        cancelarEdicion() {
            this.modoEdicion = false;
        },

        async cargarUbicacionEdicion(municipioNombre) {
            const municipio = this.municipios.find(m => m.nombre === municipioNombre);
            if (!municipio) return;
            try {
                const resP = await fetch(`/api/parroquias/${municipio.id}`);
                this.parroquiasEdicion = await resP.json();
                const resC = await fetch(`/api/ciudades/${municipio.id}`);
                this.ciudadesEdicion = await resC.json();
            } catch (e) { console.error(e); }
        },

        async guardarEdicion() {
            try {
                const res = await fetch(`/visitantes/${this.formEdicion.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formEdicion)
                });

                if (res.ok) {
                    showSuccess('Visitante actualizado correctamente');
                    this.modoEdicion = false;
                    await this.verDetalle(this.formEdicion.id);
                } else {
                    const err = await res.json();
                    console.error('Error response:', err);
                    showError(err.message || 'Error al actualizar');
                }
            } catch (e) {
                console.error('Error:', e);
                showError('Error de conexión');
            }
        },

        async eliminarVisitante(id) {
            if (!id) return;

            const confirmed = await showConfirm({
                title: '¿Eliminar visitante?',
                text: 'Esta acción no se puede deshacer y también eliminará todas sus visitas asociadas.',
                confirmButtonText: 'Sí, eliminar',
                confirmButtonColor: '#dc2626'
            });

            if (!confirmed) return;

            try {
                const response = await fetch(`/visitantes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: data.message || 'Visitante eliminado correctamente',
                        confirmButtonColor: '#2563eb'
                    });
                    window.location.reload();
                } else {
                    showError(data.message || 'Error al eliminar el visitante');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error al procesar la solicitud');
            }
        }
    };
};

// ============================================
// FUNCIÓN MODAL GLOBAL
// ============================================
window.modalGlobal = function() {
    return {
        show: false,
        visitante: null,
        visitanteId: null,


        abrir(visitanteData, id) {
            this.visitante = visitanteData;
            this.visitanteId = id;
            this.show = true;
            document.body.style.overflow = 'hidden';
        },

        cerrar() {
            this.show = false;
            this.visitante = null;
            this.visitanteId = null;
            document.body.style.overflow = '';
        },

        async eliminarVisitante() {
            const confirmed = await showConfirm({
                title: '¿Eliminar visitante?',
                text: 'Esta acción no se puede deshacer y también eliminará todas sus visitas asociadas.',
                confirmButtonText: 'Sí, eliminar',
                confirmButtonColor: '#dc2626'
            });

            if (!confirmed) return;

            try {
                const response = await fetch(`/visitantes/${this.visitanteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: data.message || 'Visitante eliminado correctamente',
                        confirmButtonColor: '#2563eb'
                    });
                    window.location.reload();
                } else {
                    showError(data.message || 'Error al eliminar el visitante');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error al procesar la solicitud');
            }
        }
    };
};

// ============================================
// INICIALIZACIÓN DE GRÁFICOS
// ============================================
function initDashboardChart() {
    const chartElement = document.getElementById('chartTiposVisitante');
    if (!chartElement) return;

    const chartData = window.chartDashboardData;
    if (!chartData) return;

    const ctx = chartElement.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.tiposLabels || [],
            datasets: [{
                label: 'Cantidad de Visitas',
                data: chartData.tiposData || [],
                backgroundColor: chartData.tiposColors || [],
                borderWidth: 0,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { display: false } }
            }
        }
    });
}

function initReportesCharts() {
    const reportData = window.chartReportesData;
    if (!reportData) return;

    ['radarChart', 'flujoHorarioChart', 'diasChart'].forEach(id => {
        const element = document.getElementById(id);
        if (element && element.chart) {
            element.chart.destroy();
            element.chart = null;
        }
    });

    // Radar Chart
    const radarElement = document.getElementById('radarChart');
    if (radarElement && reportData.salasLabels) {
        const ctx = radarElement.getContext('2d');
        radarElement.chart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: reportData.salasLabels,
                datasets: [{
                    label: 'Cantidad de visitas',
                    data: reportData.salasData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgb(54, 162, 235)',
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, backdropColor: 'transparent' },
                        grid: { color: 'rgba(0, 0, 0, 0.1)' }
                    }
                },
                plugins: {
                    tooltip: {
                        filter: function(tooltipItem) {
                            return tooltipItem.raw > 0;
                        },
                        callbacks: {
                            label: (ctx) => `${ctx.raw} visitas`
                        }
                    },
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Flujo Horario Chart
    const flujoElement = document.getElementById('flujoHorarioChart');
    if (flujoElement && reportData.flujoHorario) {
        const flujoData = reportData.flujoHorario;
        const maxValue = Math.max(...flujoData);
        const maxIndex = flujoData.indexOf(maxValue);

        const backgroundColors = flujoData.map((v, i) => {
            if (v === 0) return 'rgba(156, 163, 175, 0.5)';
            if (i === maxIndex) return 'rgba(239, 68, 68, 0.8)';
            return 'rgba(59, 130, 246, 0.6)';
        });

        const ctx = flujoElement.getContext('2d');
        flujoElement.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.horasLabels || [],
                datasets: [{
                    label: 'Cantidad de visitas',
                    data: flujoData,
                    backgroundColor: backgroundColors,
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                let label = `Visitas: ${ctx.raw}`;
                                if (ctx.raw === 0) label += ' (Sin visitas)';
                                if (ctx.dataIndex === maxIndex) label += ' ⭐ Hora pico';
                                return label;
                            }
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e5e7eb' },
                        title: { display: true, text: 'Número de visitas' }
                    },
                    x: {
                        grid: { display: false },
                        title: { display: true, text: 'Hora del día' }
                    }
                }
            }
        });
    }

    // Días Chart
    const diasElement = document.getElementById('diasChart');
    if (diasElement && reportData.diasData) {
        const diasData = reportData.diasData;
        const maxDiaValue = Math.max(...diasData);

        const diasColors = diasData.map(v =>
            v === maxDiaValue && maxDiaValue > 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(59, 130, 246, 0.6)'
        );

        const ctx = diasElement.getContext('2d');
        diasElement.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.diasLabels || ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                datasets: [{
                    label: 'Visitas',
                    data: diasData,
                    backgroundColor: diasColors,
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                let label = `Visitas: ${ctx.raw}`;
                                if (ctx.raw === maxDiaValue && maxDiaValue > 0) label += ' ⭐ Día más concurrido';
                                return label;
                            }
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

// ============================================
// EVENT LISTENERS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initDashboardChart();
    initReportesCharts();
});

document.addEventListener('alpine:initialized', function() {
    initDashboardChart();
    initReportesCharts();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.querySelector('[x-data="modalGlobal()"]');
        if (modal && modal.__x) {
            modal.__x.$data.cerrar();
        }
    }
});

// ============================================
// CRUD MANAGER GENÉRICO
// ============================================
window.crudManager = function(baseUrl, ...fields) {
    return {
        modalOpen: false,
        editing: false,
        itemId: null,
        form: {},

        init() {
            fields.forEach(f => { this.form[f] = ''; });
        },

        hasField(name) {
            return fields.includes(name);
        },

        openModal() {
            this.editing = false;
            this.itemId = null;
            fields.forEach(f => { this.form[f] = f === 'color' ? '#3B82F6' : ''; });
            this.modalOpen = true;
        },

        editItem(id, ...values) {
            this.editing = true;
            this.itemId = id;
            fields.forEach((f, i) => { this.form[f] = values[i] || ''; });
            this.modalOpen = true;
        },

        async save() {
            const url = this.editing ? `/${baseUrl}/${this.itemId}` : `/${baseUrl}`;
            const method = this.editing ? 'PUT' : 'POST';
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                if (res.ok) {
                    window.location.reload();
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión');
            }
        },

        async deleteItem(id) {
            if (await showConfirm({ title: '¿Eliminar?', text: 'Esta acción no se puede deshacer' })) {
                try {
                    const res = await fetch(`/${baseUrl}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) window.location.reload();
                    else alert('No se pudo eliminar');
                } catch (e) {
                    alert('Error');
                }
            }
        }
    };
};

// ============================================
// INICIAR ALPINE
// ============================================
Alpine.start();

// Exportar funciones helper para uso global
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showConfirm = showConfirm;
window.showToast = showToast;
