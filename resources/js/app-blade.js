/**
 * Entry point para vistas Blade (Alpine.js, charts, etc.).
 * Las rutas que usan Inertia cargan app.js; las que siguen en Blade cargan este archivo.
 */
import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Calendar } from '@fullcalendar/core';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#mapOne')) {
        import('./components/map').then((m) => m.initMap());
    }
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then((m) => m.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then((m) => m.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then((m) => m.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then((m) => m.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then((m) => m.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then((m) => m.initChartThirteen());
    }
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then((m) => m.calendarInit());
    }
    if (document.querySelector('[data-module="consulta-precios"]')) {
        import('xlsx').then((mod) => {
            window.XLSX = mod;
        });
    }
});
