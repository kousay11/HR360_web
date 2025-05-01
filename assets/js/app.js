import '../css/app.css';
import 'bootstrap';
import { Calendar } from '@fullcalendar/core';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import interactionPlugin from '@fullcalendar/interaction';

// Initialisation FullCalendar
window.initCalendar = (elementId) => {
    return new Calendar(document.getElementById(elementId), {
        plugins: [resourceTimelinePlugin, interactionPlugin],
        // Votre configuration ici
    });
};