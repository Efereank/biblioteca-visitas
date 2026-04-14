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

// Función helper para alertas de éxito
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: message,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Aceptar'
    });
}

// Función helper para alertas de error
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Aceptar'
    });
}

// Función helper para alertas de advertencia
function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: message,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Aceptar'
    });
}

// Función helper para confirmación
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

// Función para toast de éxito
function showToast(message, icon = 'success') {
    Toast.fire({
        icon: icon,
        title: message
    });
}

// FUNCIÓN WIZARD PARA REGISTRO DE VISITAS
window.wizard = function() {
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
            nombres: '',
            apellidos: '',
            email: '',
            telefono: '',
            genero: '',
            fecha_nacimiento: '',
            institucion: '',
            tipo_visitante_id: '',
            proposito_id: '',
            observaciones: '',
            actividades_ids: []
        },
        visitanteEncontrado: false,
        visitante: null,
        mensajeCedula: '',
        errorCedula: '',
        tiposVisitante: [],
        propositos: [],
        actividades: [],
        verificandoVisitaActiva: false,
        cedulaVerificada: false,
        cedulaDisponible: false,

        storeUrl: '',
        historialUrl: '',

        get cedulaCompleta() {
            return this.form.prefijo + this.form.numeroCedula;
        },

        init(tipos, propositos, actividades, visitantePrecargado) {
            this.tiposVisitante = tipos;
            this.propositos = propositos;
            this.actividades = actividades;

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
            }
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
                        this.visitanteEncontrado = false;
                        this.visitante = null;
                        this.mensajeCedula = '⚠️ Este visitante ya tiene una visita activa.';
                        this.errorCedula = 'El visitante tiene una visita activa';
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

        async irAPasoDatos() {
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
                    showWarning('Este visitante ya tiene una visita activa. Debe registrar la salida primero.');
                    this.visitanteEncontrado = false;
                    this.visitante = null;
                    this.mensajeCedula = '⚠️ El visitante tiene una visita activa';
                    this.errorCedula = 'El visitante tiene una visita activa';
                    return;
                }

                this.form.nombres = this.visitante.nombres;
                this.form.apellidos = this.visitante.apellidos;
                this.form.email = this.visitante.email || '';
                this.form.telefono = this.visitante.telefono || '';
                this.form.genero = this.visitante.genero || '';
                this.form.fecha_nacimiento = this.visitante.fecha_nacimiento || '';
                this.form.institucion = this.visitante.institucion || '';
                this.form.tipo_visitante_id = this.visitante.tipo_visitante_id;

                this.currentStep = 2;
            } catch (e) {
                console.error(e);
                showError('Error al verificar visita activa');
            }
        },

        siguiente() {
            if (this.currentStep === 1) {
                if (!this.form.nombres || !this.form.apellidos) {
                    showWarning('Nombres y apellidos son obligatorios');
                    return;
                }
                if (!this.form.tipo_visitante_id) {
                    showWarning('Seleccione el tipo de visitante');
                    return;
                }
                this.currentStep++;
            } else if (this.currentStep === 2) {
                if (!this.form.proposito_id) {
                    showWarning('Seleccione un propósito');
                    return;
                }
                this.currentStep++;
            }
        },

        async submitForm() {
            let payload = {};
            this.form.cedula = this.cedulaCompleta;

            if (this.visitanteEncontrado && this.visitante) {
                try {
                    const res = await fetch(`/api/visitantes/${this.visitante.id}/visita-activa`);
                    const data = await res.json();
                    if (data.tieneVisitaActiva) {
                        showWarning('El visitante ya tiene una visita activa.');
                        return;
                    }
                } catch (e) {
                    console.error(e);
                }
                payload.visitante_id = this.visitante.id;
            } else {
                if (!this.validarCedulaLocal()) {
                    showError(this.errorCedula);
                    return;
                }

                payload.visitante_nuevo = {
                    cedula: this.cedulaCompleta,
                    nombres: this.form.nombres,
                    apellidos: this.form.apellidos,
                    email: this.form.email || null,
                    telefono: this.form.telefono || null,
                    genero: this.form.genero || null,
                    fecha_nacimiento: this.form.fecha_nacimiento || null,
                    institucion: this.form.institucion || null,
                    tipo_visitante_id: this.form.tipo_visitante_id
                };
            }

            payload.proposito_id = this.form.proposito_id;
            payload.observaciones = this.form.observaciones || null;
            payload.actividades_ids = this.form.actividades_ids;

            // Mostrar loading
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
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Visita registrada!',
                        text: data.message || 'Visita registrada exitosamente',
                        confirmButtonColor: '#2563eb'
                    });
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

// FUNCIÓN VISITANTES MANAGER
window.visitantesManager = function() {
    return {
        modalAbierto: false,
        cargandoDetalle: false,
        visitanteSeleccionado: null,

        async verDetalle(id) {
            this.modalAbierto = true;
            this.cargandoDetalle = true;
            this.visitanteSeleccionado = null;

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

// FUNCIÓN MODAL GLOBAL
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

// INICIALIZACIÓN DE GRÁFICOS
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

    // Radar Chart
    const radarElement = document.getElementById('radarChart');
    if (radarElement && reportData.actividadesLabels) {
        const ctx = radarElement.getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: reportData.actividadesLabels,
                datasets: [{
                    label: 'Cantidad de veces seleccionada',
                    data: reportData.actividadesData,
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

        const borderColors = flujoData.map((v, i) => {
            if (v === 0) return 'rgb(156, 163, 175)';
            if (i === maxIndex) return 'rgb(239, 68, 68)';
            return 'rgb(59, 130, 246)';
        });

        const ctx = flujoElement.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.horasLabels || [],
                datasets: [{
                    label: 'Cantidad de visitas',
                    data: flujoData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
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
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reportData.diasLabels || ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                datasets: [{
                    label: 'Visitas',
                    data: diasData,
                    backgroundColor: diasColors,
                    borderColor: diasColors.map(c => c.replace('0.6', '1').replace('0.8', '1')),
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

// EVENT LISTENERS
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

// INICIAR ALPINE
Alpine.start();

// Exportar funciones helper para uso global
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showConfirm = showConfirm;
window.showToast = showToast;
