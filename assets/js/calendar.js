import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction'

// Exporter pour utilisation globale
window.FullCalendar = {
  Calendar,
  resourceTimelinePlugin,
  interactionPlugin
}