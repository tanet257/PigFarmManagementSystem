import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";


flatpickr("#dateTimeInput", {
    enableTime: true,
    dateFormat: "d/m/Y H:i", // แสดงผลแบบไทย
    maxDate: "today", // ไม่อนุญาตวันในอนาคต
    time_24hr: true, // ใช้เวลา 24 ชั่วโมง
});



window.Alpine = Alpine;

Alpine.plugin(focus);

Alpine.start();
