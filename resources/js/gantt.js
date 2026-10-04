import axios from 'axios';

const MODOS = { Día: 'Day', Semana: 'Week', Mes: 'Month' };

const fecha = (d) => {
    const z = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
};

/**
 * Componente Alpine para el diagrama de Gantt.
 * config: { tareas, urlMover (con ":id"), soloLectura, urlClick (con ":id", opcional) }
 */
export default (config) => ({
    modo: 'Semana',
    modos: Object.keys(MODOS),
    error: null,
    gantt: null,

    async init() {
        if (!config.tareas.length) return;

        const { default: Gantt } = await import('frappe-gantt');

        this.gantt = new Gantt(this.$refs.lienzo, config.tareas, {
            language: 'es',
            view_mode: MODOS[this.modo],
            bar_height: 26,
            padding: 16,
            container_height: Math.min(640, config.tareas.length * 42 + 120),
            readonly: !!config.soloLectura,
            today_button: false,
            popup_on: 'hover',
            infinite_padding: false,
            on_click: (tarea) => {
                if (config.urlClick) window.location = config.urlClick.replace(':id', tarea.id);
            },
            on_date_change: (tarea, inicio, fin) =>
                this.guardar(tarea.id, { fecha_inicio: fecha(inicio), fecha_fin: fecha(fin) }),
            on_progress_change: (tarea, avance) => this.guardar(tarea.id, { avance: Math.round(avance) }),
        });
    },

    cambiarModo(modo) {
        this.modo = modo;
        this.gantt?.change_view_mode(MODOS[modo]);
    },

    async guardar(id, datos) {
        this.error = null;
        try {
            await axios.patch(config.urlMover.replace(':id', id), datos);
        } catch (e) {
            this.error = e.response?.data?.message ?? 'No se pudo guardar el cambio. Recargá la página.';
        }
    },
});
