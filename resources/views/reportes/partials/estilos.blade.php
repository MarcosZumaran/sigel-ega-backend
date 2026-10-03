<style>
    @page { size: A4 {{ ($orientacion ?? 'vertical') === 'horizontal' ? 'landscape' : 'portrait' }}; margin: 14mm 12mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1E293B; margin: 0; }

    /* Membrete */
    .membrete { text-align: center; border-bottom: 2px solid #1E3A8A; padding-bottom: 5px; margin-bottom: 8px; }
    .membrete-linea-1 { color: #1E3A8A; font-size: 12px; font-weight: bold; }
    .membrete-linea-2 { color: #334155; font-size: 8px; margin-top: 2px; }
    .membrete-linea-3 { color: #64748B; font-size: 7.5px; margin-top: 1px; }
    .membrete-sep { color: #94A3B8; margin: 0 3px; }
    .membrete-titulo { margin-top: 5px; }
    .membrete-titulo h1 { color: #1E3A8A; font-size: 13px; margin: 0; letter-spacing: 0.5px; }
    .membrete-titulo h2 { color: #475569; font-size: 9px; margin: 2px 0 0 0; font-weight: normal; }

    /* Tablas */
    table { width: 100%; border-collapse: collapse; }
    th { background: #1E3A8A; color: #fff; padding: 4px; text-align: center; font-size: 8px; }
    td { padding: 3px; border: 1px solid #CBD5E1; font-size: 8px; }
    tr:nth-child(even) td { background: #F8FAFC; }
    td.label { background: #F1F5F9; font-weight: bold; color: #475569; }

    /* Niveles CNEB */
    .AD { background: #D1FAE5 !important; color: #065F46; font-weight: bold; text-align: center; }
    .A  { background: #DBEAFE !important; color: #1E40AF; font-weight: bold; text-align: center; }
    .B  { background: #FEF3C7 !important; color: #92400E; font-weight: bold; text-align: center; }
    .C  { background: #FECACA !important; color: #991B1B; font-weight: bold; text-align: center; }

    /* Firmas */
    .firmas { margin-top: 25px; }
    .firmas table { border: none; }
    .firmas td { border: none; text-align: center; padding: 20px 10px 0; }
    .firma-linea { border-top: 1px solid #000; width: 180px; margin: 0 auto 3px; padding-top: 3px; font-size: 8px; }

    /* Pie */
    .pie { font-size: 6.5px; color: #94A3B8; text-align: center; margin-top: 10px; }

    /* Datos */
    .datos { margin-bottom: 6px; }
    .datos table { border: 1px solid #CBD5E1; }
    .datos td.label { width: 20%; }

    /* Compatibilidad con vistas existentes (no alterar el contenido) */
    .datos b { color: #1E3A8A; }
    .niv { font-weight: bold; text-align: center; }
    .lin { border-top: 1px solid #1e293b; margin: 0 30px; }
    .top { background: #fef9c3; font-weight: bold; }
    .area { background: #eff6ff; font-weight: bold; color: #1E3A8A; }
    .resumen { margin: 8px 0 0; font-size: 10px; font-weight: bold; color: #1E3A8A; }
    .pg:after { content: " · Página " counter(page) " de " counter(pages); }
    td.valor { background: #FFFFFF; }

    /* Secciones FUM */
    .seccion { margin-top: 6px; }
    .seccion-titulo { background: #1E3A8A; color: #fff; padding: 3px 6px; font-weight: bold; font-size: 8px; }
    .foto { width: 90px; height: 120px; border: 1px solid #CBD5E1; background: #F8FAFC; text-align: center; font-size: 7px; color: #94A3B8; padding-top: 50px; }
    .firma { text-align: center; margin-top: 20px; }
    .firma-line { border-top: 1px solid #000; width: 200px; margin: 0 auto 2px; padding-top: 3px; font-size: 8px; }
</style>
