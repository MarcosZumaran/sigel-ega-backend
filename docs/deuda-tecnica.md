# Deuda Técnica — SIGEL-EGA

## BUG-09: Formulario de estudiante asume un solo apoderado

**Descripción:** El formulario de estudiantes permite asignar UN solo
apoderado. Pero el modelo de datos soporta N:M (un estudiante puede tener
múltiples apoderados: padre, madre, tutor).

**Impacto:** Medio — funcional pero limita casos de uso reales.

**Cambio requerido:**
- Frontend: reemplazar FormControl "padre" por FormArray "apoderados"
- Cada apoderado: { padre_id, parentesco_id, es_principal }
- UI: botón "Agregar apoderado" + lista dinámica con eliminar
- Backend: endpoint batch para vincular múltiples apoderados

**Esfuerzo estimado:** 8-12 horas.

**Prioridad:** Baja (no bloquea casos de uso comunes).
