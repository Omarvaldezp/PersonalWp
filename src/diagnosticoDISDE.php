<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cuestionario Diagnóstico · Curso Propedéutico DISDE · FCA-UAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root{ --navy:#1B365D; --blue:#2E5A9C; --gold:#C9A227; --lightblue:#E8EEF7; --grey:#F5F5F5; }
  *{ box-sizing:border-box; }
  body{ font-family:Arial, sans-serif; margin:0; background:#f5f5f5; color:#333; line-height:1.6; }
  .wrap{ max-width:900px; margin:0 auto; padding:0 0 60px; }
  .header{ background:linear-gradient(135deg,var(--navy) 0%,var(--blue) 100%); color:#fff; padding:40px 25px; text-align:center; }
  .header i.brand{ font-size:2.4em; display:block; margin-bottom:12px; }
  .header h1{ margin:0; font-size:1.9em; }
  .header p{ margin:10px 0 0; color:var(--gold); font-size:1.05em; }
  .section{ background:#fff; border:1px solid #ddd; padding:25px; margin:0 15px; }
  .intro{ border-top:none; }
  .intro p{ font-size:0.97em; }
  .nota{ background:var(--lightblue); border-left:4px solid var(--navy); padding:15px 18px; border-radius:0 8px 8px 0; margin:18px 0; font-size:0.93em; }
  h2.area{ color:var(--blue); border-bottom:2px solid var(--gold); padding-bottom:10px; margin:30px 15px 0; }
  .area-tag{ display:inline-block; background:var(--navy); color:#fff; font-size:0.7em; padding:3px 10px; border-radius:12px; vertical-align:middle; margin-left:8px; }
  .field{ margin:18px 0; }
  .field label.q{ display:block; font-weight:bold; color:var(--navy); margin-bottom:8px; }
  .field input[type=text], .field input[type=email], .field select{ width:100%; padding:11px 12px; border:1px solid #bbb; border-radius:6px; font-size:0.95em; font-family:inherit; }
  .opts label{ display:block; padding:9px 12px; border:1px solid #ddd; border-radius:6px; margin-bottom:7px; cursor:pointer; font-size:0.94em; transition:.15s; }
  .opts label:hover{ background:var(--lightblue); border-color:var(--blue); }
  .opts input{ margin-right:10px; }
  .likert{ display:flex; gap:8px; flex-wrap:wrap; }
  .likert label{ flex:1; min-width:110px; text-align:center; }
  .badge-auto, .badge-con{ display:inline-block; font-size:0.68em; font-weight:bold; padding:2px 9px; border-radius:10px; margin-left:6px; vertical-align:middle; }
  .badge-auto{ background:#E3F2FD; color:#1565C0; border:1px solid #90CAF9; }
  .badge-con{ background:#FFF6E0; color:#8a6d00; border:1px solid var(--gold); }
  .actions{ text-align:center; margin:30px 15px 0; }
  .btn{ background:var(--navy); color:#fff; border:none; padding:14px 30px; font-size:1.05em; border-radius:8px; cursor:pointer; font-family:inherit; }
  .btn:hover{ background:#15294a; }
  .btn.sec{ background:var(--gold); color:var(--navy); margin-left:10px; }
  .error{ color:#C62828; font-size:0.85em; margin-top:5px; display:none; }
  .results{ display:none; background:#fff; border:2px solid var(--gold); border-radius:10px; padding:25px; margin:25px 15px 0; }
  .results h2{ color:var(--navy); margin-top:0; }
  .score-big{ font-size:2.4em; font-weight:bold; color:var(--blue); }
  table.res{ width:100%; border-collapse:collapse; margin:18px 0; font-size:0.92em; }
  table.res th{ background:var(--navy); color:#fff; padding:10px; text-align:left; }
  table.res td{ padding:9px 10px; border-bottom:1px solid #ddd; }
  .bar{ background:#eee; border-radius:6px; height:14px; overflow:hidden; }
  .bar > span{ display:block; height:100%; background:linear-gradient(90deg,var(--gold),#E8C547); }
  .footer{ background:var(--navy); color:#fff; text-align:center; padding:20px; margin:40px 15px 0; border-radius:0 0 10px 10px; font-size:0.9em; }
  .footer p{ margin:4px 0; opacity:.95; }
  .req{ color:#C62828; }
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <i class="fas fa-clipboard-question brand"></i>
    <h1>Cuestionario Diagnóstico</h1>
    <p>Curso Propedéutico · Doctorado en Ciencias en Innovación Social y Desarrollo Económico (DISDE) · FCA-UAS</p>
    <p style="margin:8px 0 0; font-size:0.9em; color:#fff; opacity:0.9;"><i class="fas fa-chalkboard-user" style="margin-right:6px;"></i>Facilitador: Dr. Omar Valdez Palazuelos</p>
  </div>

  <div class="section intro">
    <p>Este cuestionario es de <strong>diagnóstico</strong>: no afecta tu calificación. Nos ayuda a conocer tu punto de partida y tu familiaridad con el programa al que deseas ingresar, para acompañarte mejor durante el curso. Responde con honestidad; toma alrededor de 10 minutos.</p>
    <div class="nota">
      <i class="fas fa-circle-info" style="color:var(--gold); margin-right:6px;"></i>
      Encontrarás dos tipos de preguntas: <span class="badge-auto">Autoevaluación</span> (tu percepción de tu propio nivel) y <span class="badge-con">Conocimiento</span> (con respuesta correcta). Al final verás un resumen de tus resultados.
    </div>
  </div>

  <form id="diag">

    <!-- DATOS DEL PARTICIPANTE -->
    <h2 class="area"><i class="fas fa-id-card" style="color:var(--gold); margin-right:8px;"></i>Datos del participante</h2>
    <div class="section">
      <div class="field">
        <label class="q" for="nombre">Nombre completo <span class="req">*</span></label>
        <input type="text" id="nombre" name="nombre" autocomplete="name" required>
        <div class="error" data-for="nombre">Por favor escribe tu nombre completo.</div>
      </div>
      <div class="field">
        <label class="q" for="correo">Correo electrónico <span class="req">*</span></label>
        <input type="email" id="correo" name="correo" autocomplete="email" required>
        <div class="error" data-for="correo">Escribe un correo electrónico válido.</div>
      </div>
      <div class="field">
        <label class="q" for="grado">Último grado académico <span class="req">*</span></label>
        <select id="grado" name="grado" required>
          <option value="">Selecciona una opción…</option>
          <option>Maestría en curso</option>
          <option>Maestría concluida (sin título)</option>
          <option>Maestría titulada</option>
          <option>Otro</option>
        </select>
        <div class="error" data-for="grado">Selecciona tu último grado académico.</div>
      </div>
      <div class="field">
        <label class="q" for="carrera">Carrera de licenciatura que cursaste <span class="req">*</span></label>
        <input type="text" id="carrera" name="carrera" placeholder="Ej. Lic. en Administración / Economía / Trabajo Social" required>
        <div class="error" data-for="carrera">Indica la carrera de licenciatura que cursaste.</div>
      </div>
      <div class="field">
        <label class="q" for="area_form">Área o programa de tu formación (maestría) <span class="req">*</span></label>
        <input type="text" id="area_form" name="area_form" placeholder="Ej. Maestría en Administración / Políticas Públicas / Economía" required>
        <div class="error" data-for="area_form">Indica el área o programa de tu formación.</div>
      </div>
      <div class="field">
        <label class="q" for="institucion">Institución de procedencia</label>
        <input type="text" id="institucion" name="institucion" placeholder="Universidad o institución">
      </div>
    </div>

    <!-- ÁREA 1: DISDE -->
    <h2 class="area"><i class="fas fa-compass" style="color:var(--gold); margin-right:8px;"></i>1. Conocimiento del programa (DISDE)<span class="area-tag">Área 1</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">1.1 ¿Qué tanto conoces el plan de estudios y el perfil de ingreso del DISDE? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="A1">
          <label><input type="radio" name="A1" value="0">Nada</label>
          <label><input type="radio" name="A1" value="1">Poco</label>
          <label><input type="radio" name="A1" value="2">Medio</label>
          <label><input type="radio" name="A1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">1.2 ¿Qué tan claro tienes con cuál Línea de Investigación e Incidencia Social (LIES) se relacionaría tu tema? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="A2">
          <label><input type="radio" name="A2" value="0">Nada</label>
          <label><input type="radio" name="A2" value="1">Poco</label>
          <label><input type="radio" name="A2" value="2">Medio</label>
          <label><input type="radio" name="A2" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="investigacion">
        <label class="q">1.3 El DISDE es un doctorado con orientación a… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="A3">
          <label><input type="radio" name="A3" value="investigacion">La investigación</label>
          <label><input type="radio" name="A3" value="mixta">Mixta (investigación y profesional)</label>
          <label><input type="radio" name="A3" value="profesionalizante">Profesionalizante</label>
          <label><input type="radio" name="A3" value="docencia">Únicamente la docencia</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="politicas">
        <label class="q">1.4 ¿Cuál de las siguientes es una de las dos LIES del DISDE? <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="A4">
          <label><input type="radio" name="A4" value="politicas">Políticas públicas e innovación social</label>
          <label><input type="radio" name="A4" value="gestion_emp">Gestión empresarial para la competitividad de los mercados</label>
          <label><input type="radio" name="A4" value="finanzas">Gestión de finanzas corporativas</label>
          <label><input type="radio" name="A4" value="fiscal">Auditoría y estudios fiscales</label>
        </div>
      </div>
    </div>

    <!-- ÁREA 2: EPISTEMOLOGÍA -->
    <h2 class="area"><i class="fas fa-lightbulb" style="color:var(--gold); margin-right:8px;"></i>2. Fundamentos epistemológicos<span class="area-tag">Área 2</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">2.1 ¿Qué tanto dominas los paradigmas de investigación (positivista, interpretativo, sociocrítico)? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="B1">
          <label><input type="radio" name="B1" value="0">Nada</label>
          <label><input type="radio" name="B1" value="1">Poco</label>
          <label><input type="radio" name="B1" value="2">Medio</label>
          <label><input type="radio" name="B1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="conocimiento">
        <label class="q">2.2 La epistemología estudia, fundamentalmente… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="B2">
          <label><input type="radio" name="B2" value="conocimiento">La naturaleza, el origen y la validez del conocimiento</label>
          <label><input type="radio" name="B2" value="metodos">Los métodos y técnicas para recolectar datos</label>
          <label><input type="radio" name="B2" value="moral">Las normas morales</label>
          <label><input type="radio" name="B2" value="estetica">La belleza y el arte</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="interpretativo">
        <label class="q">2.3 El paradigma que busca comprender los significados desde la perspectiva de los sujetos es el… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="B3">
          <label><input type="radio" name="B3" value="interpretativo">Interpretativo</label>
          <label><input type="radio" name="B3" value="sociocritico">Sociocrítico</label>
          <label><input type="radio" name="B3" value="positivista">Positivista</label>
          <label><input type="radio" name="B3" value="empirico">Empírico-analítico</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">2.4 ¿Qué tan capaz te sientes de justificar la postura epistemológica de una investigación? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="B4">
          <label><input type="radio" name="B4" value="0">Nada</label>
          <label><input type="radio" name="B4" value="1">Poco</label>
          <label><input type="radio" name="B4" value="2">Medio</label>
          <label><input type="radio" name="B4" value="3">Mucho</label>
        </div>
      </div>
    </div>

    <!-- ÁREA 3: METODOLOGÍA -->
    <h2 class="area"><i class="fas fa-diagram-project" style="color:var(--gold); margin-right:8px;"></i>3. Metodología de la investigación<span class="area-tag">Área 3</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">3.1 ¿Qué tan claro tienes la diferencia entre los enfoques cuantitativo, cualitativo y mixto? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="C1">
          <label><input type="radio" name="C1" value="0">Nada</label>
          <label><input type="radio" name="C1" value="1">Poco</label>
          <label><input type="radio" name="C1" value="2">Medio</label>
          <label><input type="radio" name="C1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="categorias">
        <label class="q">3.2 En un estudio cualitativo, en lugar de hipótesis y variables se suele trabajar con… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="C2">
          <label><input type="radio" name="C2" value="categorias">Categorías y supuestos</label>
          <label><input type="radio" name="C2" value="dimensiones">Dimensiones e indicadores operacionales</label>
          <label><input type="radio" name="C2" value="parametros">Parámetros poblacionales</label>
          <label><input type="radio" name="C2" value="muestreo">Muestreo probabilístico</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="correlacional">
        <label class="q">3.3 El alcance que mide la relación entre dos o más variables sin establecer causalidad es el… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="C3">
          <label><input type="radio" name="C3" value="correlacional">Correlacional</label>
          <label><input type="radio" name="C3" value="descriptivo">Descriptivo</label>
          <label><input type="radio" name="C3" value="explicativo">Explicativo</label>
          <label><input type="radio" name="C3" value="exploratorio">Exploratorio</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">3.4 ¿Qué tanto dominas la formulación de un problema, sus preguntas y objetivos? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="C4">
          <label><input type="radio" name="C4" value="0">Nada</label>
          <label><input type="radio" name="C4" value="1">Poco</label>
          <label><input type="radio" name="C4" value="2">Medio</label>
          <label><input type="radio" name="C4" value="3">Mucho</label>
        </div>
      </div>
    </div>

    <!-- ÁREA 4: ESTADÍSTICA -->
    <h2 class="area"><i class="fas fa-chart-column" style="color:var(--gold); margin-right:8px;"></i>4. Estadística y métodos cuantitativos<span class="area-tag">Área 4</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">4.1 ¿Qué tanto manejas la estadística descriptiva (media, mediana, desviación estándar)? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="D1">
          <label><input type="radio" name="D1" value="0">Nada</label>
          <label><input type="radio" name="D1" value="1">Poco</label>
          <label><input type="radio" name="D1" value="2">Medio</label>
          <label><input type="radio" name="D1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="significativo">
        <label class="q">4.2 En una prueba de hipótesis, un valor p menor a 0.05 generalmente indica… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="D2">
          <label><input type="radio" name="D2" value="significativo">Un resultado estadísticamente significativo</label>
          <label><input type="radio" name="D2" value="nula">Que se confirma (acepta) la hipótesis nula</label>
          <label><input type="radio" name="D2" value="muestra">Que la muestra es demasiado pequeña</label>
          <label><input type="radio" name="D2" value="norelacion">Ausencia total de relación</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="desviacion">
        <label class="q">4.3 La medida que indica cuánto se alejan los datos respecto de la media es… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="D3">
          <label><input type="radio" name="D3" value="desviacion">La desviación estándar</label>
          <label><input type="radio" name="D3" value="rango">El rango</label>
          <label><input type="radio" name="D3" value="mediana">La mediana</label>
          <label><input type="radio" name="D3" value="moda">La moda</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">4.4 ¿Qué tanto manejas algún software estadístico (Excel, Jamovi o SPSS)? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="D4">
          <label><input type="radio" name="D4" value="0">Nada</label>
          <label><input type="radio" name="D4" value="1">Poco</label>
          <label><input type="radio" name="D4" value="2">Medio</label>
          <label><input type="radio" name="D4" value="3">Mucho</label>
        </div>
      </div>
    </div>

    <!-- ÁREA 5: REDACCIÓN Y APA -->
    <h2 class="area"><i class="fas fa-pen-nib" style="color:var(--gold); margin-right:8px;"></i>5. Redacción académica y APA<span class="area-tag">Área 5</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">5.1 ¿Qué tanto dominas las normas APA 7 para citas y referencias? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="E1">
          <label><input type="radio" name="E1" value="0">Nada</label>
          <label><input type="radio" name="E1" value="1">Poco</label>
          <label><input type="radio" name="E1" value="2">Medio</label>
          <label><input type="radio" name="E1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="autor_anio">
        <label class="q">5.2 En APA 7, una cita en el texto (paréntesis) incluye principalmente… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="E2">
          <label><input type="radio" name="E2" value="autor_anio">El apellido del autor y el año</label>
          <label><input type="radio" name="E2" value="autor_anio_pag">El apellido del autor, el año y el número de página</label>
          <label><input type="radio" name="E2" value="titulo">El título y la editorial</label>
          <label><input type="radio" name="E2" value="pagina">Únicamente el número de página</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="organizar">
        <label class="q">5.3 Un gestor bibliográfico como Zotero o Mendeley sirve para… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="E3">
          <label><input type="radio" name="E3" value="organizar">Organizar referencias y generar citas automáticamente</label>
          <label><input type="radio" name="E3" value="plagio">Detectar similitud y posible plagio en los textos</label>
          <label><input type="radio" name="E3" value="cuali">Analizar datos cualitativos</label>
          <label><input type="radio" name="E3" value="encuestas">Diseñar y aplicar encuestas</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">5.4 ¿Qué tan cómodo te sientes redactando textos académicos extensos (artículos, proyectos)? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="E4">
          <label><input type="radio" name="E4" value="0">Nada</label>
          <label><input type="radio" name="E4" value="1">Poco</label>
          <label><input type="radio" name="E4" value="2">Medio</label>
          <label><input type="radio" name="E4" value="3">Mucho</label>
        </div>
      </div>
    </div>

    <!-- ÁREA 6: TIC e IA -->
    <h2 class="area"><i class="fas fa-robot" style="color:var(--gold); margin-right:8px;"></i>6. TIC e inteligencia artificial<span class="area-tag">Área 6</span></h2>
    <div class="section">
      <div class="field" data-q="auto">
        <label class="q">6.1 ¿Qué tanto manejas plataformas educativas (Moodle) y herramientas digitales de estudio? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="F1">
          <label><input type="radio" name="F1" value="0">Nada</label>
          <label><input type="radio" name="F1" value="1">Poco</label>
          <label><input type="radio" name="F1" value="2">Medio</label>
          <label><input type="radio" name="F1" value="3">Mucho</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="inventar">
        <label class="q">6.2 En el uso ético de la IA para investigación, ¿cuál de estas prácticas NO es aceptable? <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="F2">
          <label><input type="radio" name="F2" value="inventar">Presentar texto generado por IA como propio e inventar referencias</label>
          <label><input type="radio" name="F2" value="borrador">Usar IA para un borrador y luego reescribirlo y declararlo con transparencia</label>
          <label><input type="radio" name="F2" value="verificar">Verificar las fuentes que sugiere la IA</label>
          <label><input type="radio" name="F2" value="declarar">Declarar el uso de IA en el trabajo</label>
        </div>
      </div>
      <div class="field" data-q="con" data-correct="verificar">
        <label class="q">6.3 Cuando la IA propone una referencia bibliográfica, lo correcto es… <span class="badge-con">Conocimiento</span></label>
        <div class="opts" data-name="F3">
          <label><input type="radio" name="F3" value="verificar">Verificarla en la fuente original antes de usarla</label>
          <label><input type="radio" name="F3" value="formato">Citarla si el formato APA está bien escrito</label>
          <label><input type="radio" name="F3" value="asumir">Asumir que es correcta porque la dio la IA</label>
          <label><input type="radio" name="F3" value="citar">Citarla directamente sin revisarla</label>
        </div>
      </div>
      <div class="field" data-q="auto">
        <label class="q">6.4 ¿Qué tanto has usado IA generativa (asistentes tipo chat) en tareas académicas? <span class="badge-auto">Autoevaluación</span></label>
        <div class="opts likert" data-name="F4">
          <label><input type="radio" name="F4" value="0">Nada</label>
          <label><input type="radio" name="F4" value="1">Poco</label>
          <label><input type="radio" name="F4" value="2">Medio</label>
          <label><input type="radio" name="F4" value="3">Mucho</label>
        </div>
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn"><i class="fas fa-paper-plane" style="margin-right:8px;"></i>Enviar y ver mi diagnóstico</button>
    </div>
  </form>

  <!-- RESULTADOS -->
  <div class="results" id="results">
    <h2><i class="fas fa-square-poll-vertical" style="color:var(--gold); margin-right:8px;"></i>Tu diagnóstico</h2>
    <p id="res-saludo"></p>
    <p>Conocimiento (respuestas correctas): <span class="score-big" id="res-score">0</span> <span style="font-size:1.1em;">/ 12</span></p>
    <table class="res">
      <thead><tr><th>Área</th><th>Conocimiento</th><th>Autopercepción</th></tr></thead>
      <tbody id="res-body"></tbody>
    </table>
    <div class="nota" id="res-reco"></div>
    <div class="actions" style="margin-top:10px;">
      <button type="button" class="btn sec" id="btn-download"><i class="fas fa-download" style="margin-right:8px;"></i>Descargar mis resultados</button>
    </div>
  </div>

  <div class="footer">
    <p><i class="fas fa-graduation-cap" style="margin-right:6px;"></i>Curso Propedéutico DISDE · Facultad de Contaduría y Administración · UAS</p>
    <p>Facilitador: Dr. Omar Valdez Palazuelos</p>
  </div>

</div>

<script>
/* ====== CONFIGURACIÓN OPCIONAL ======
   Si quieres recibir las respuestas en un backend (Google Forms, Formspree, etc.),
   coloca aquí la URL del endpoint. Si lo dejas vacío, el cuestionario funciona
   igual: muestra el diagnóstico en pantalla y permite descargar los resultados. */
const FORM_ENDPOINT = "/api/controllers/diagnostico-disde.php"; // Backend API
/* ===================================== */

const KNOWLEDGE = {
  A3:"investigacion", A4:"politicas",
  B2:"conocimiento", B3:"interpretativo",
  C2:"categorias", C3:"correlacional",
  D2:"significativo", D3:"desviacion",
  E2:"autor_anio", E3:"organizar",
  F2:"inventar", F3:"verificar"
};
const AREAS = {
  "1. Programa DISDE": { auto:["A1","A2"], con:["A3","A4"] },
  "2. Epistemología":  { auto:["B1","B4"], con:["B2","B3"] },
  "3. Metodología":    { auto:["C1","C4"], con:["C2","C3"] },
  "4. Estadística":    { auto:["D1","D4"], con:["D2","D3"] },
  "5. Redacción y APA":{ auto:["E1","E4"], con:["E2","E3"] },
  "6. TIC e IA":       { auto:["F1","F4"], con:["F2","F3"] }
};
const NIVEL = ["Sin experiencia","Inicial","Intermedio","Avanzado"];

const form = document.getElementById("diag");

function val(name){ const el = form.querySelector('[name="'+name+'"]:checked'); return el ? el.value : null; }
function showError(id, show){ const e = form.querySelector('.error[data-for="'+id+'"]'); if(e) e.style.display = show ? "block" : "none"; }

form.addEventListener("submit", function(ev){
  ev.preventDefault();
  let ok = true;

  // Validar datos del participante
  ["nombre","correo","grado","carrera","area_form"].forEach(function(id){
    const el = document.getElementById(id);
    const empty = !el.value.trim();
    const bademail = (id==="correo" && el.value && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(el.value));
    showError(id, empty || bademail);
    if(empty || bademail) ok = false;
  });

  // Validar que todas las preguntas estén respondidas
  let firstUnanswered = null;
  Object.keys(AREAS).forEach(function(a){
    AREAS[a].auto.concat(AREAS[a].con).forEach(function(n){
      if(!val(n) && !firstUnanswered) firstUnanswered = n;
    });
  });
  if(firstUnanswered){ ok = false; }

  if(!ok){
    if(firstUnanswered){
      alert("Aún faltan preguntas por responder. Por favor revisa el cuestionario.");
      const node = form.querySelector('[name="'+firstUnanswered+'"]');
      if(node) node.closest('.field').scrollIntoView({behavior:"smooth", block:"center"});
    } else {
      const firstErr = form.querySelector('.error[style*="block"]');
      if(firstErr) firstErr.scrollIntoView({behavior:"smooth", block:"center"});
    }
    return;
  }

  // Calcular resultados
  let totalCorrect = 0;
  const rows = [];
  Object.keys(AREAS).forEach(function(a){
    let cCorrect = 0;
    AREAS[a].con.forEach(function(n){ if(val(n) === KNOWLEDGE[n]) cCorrect++; });
    totalCorrect += cCorrect;
    let autoSum = 0;
    AREAS[a].auto.forEach(function(n){ autoSum += parseInt(val(n),10); });
    const autoAvg = autoSum / AREAS[a].auto.length; // 0..3
    rows.push({ area:a, con:cCorrect, conMax:AREAS[a].con.length, autoAvg:autoAvg });
  });

  // Render
  document.getElementById("res-score").textContent = totalCorrect;
  document.getElementById("res-saludo").innerHTML =
    "Gracias, <strong>" + document.getElementById("nombre").value.trim() + "</strong>. Este es tu punto de partida; lo usaremos para acompañarte durante el curso.";
  const body = document.getElementById("res-body");
  body.innerHTML = "";
  rows.forEach(function(r){
    const pct = Math.round((r.autoAvg/3)*100);
    body.innerHTML += "<tr><td>"+r.area+"</td><td>"+r.con+" / "+r.conMax+"</td>"+
      "<td>"+NIVEL[Math.round(r.autoAvg)]+
      "<div class='bar'><span style='width:"+pct+"%'></span></div></td></tr>";
  });

  let reco;
  if(totalCorrect >= 10) reco = "Tienes una base sólida. El curso te ayudará a consolidar tu anteproyecto y a afinar detalles metodológicos.";
  else if(totalCorrect >= 6) reco = "Tienes una base intermedia. Aprovecha las sesiones de métodos y de redacción para reforzar tus áreas más débiles.";
  else reco = "Es un excelente momento para nivelar fundamentos. Apóyate en las lecturas y tutorías del curso desde la primera semana.";
  document.getElementById("res-reco").innerHTML = "<i class='fas fa-lightbulb' style='color:var(--gold); margin-right:6px;'></i>" + reco;

  // Guardar payload para descarga / envío
  const payload = {
    nombre: document.getElementById("nombre").value.trim(),
    correo: document.getElementById("correo").value.trim(),
    grado: document.getElementById("grado").value,
    carrera_licenciatura: document.getElementById("carrera").value.trim(),
    area_formacion: document.getElementById("area_form").value.trim(),
    institucion: document.getElementById("institucion").value.trim(),
    conocimiento_total: totalCorrect,
    detalle: rows.map(function(r){ return { area:r.area, conocimiento:r.con+"/"+r.conMax, autopercepcion:NIVEL[Math.round(r.autoAvg)] }; }),
    respuestas: {},
    fecha: new Date().toLocaleString()
  };
  Object.keys(AREAS).forEach(function(a){
    AREAS[a].auto.concat(AREAS[a].con).forEach(function(n){ payload.respuestas[n] = val(n); });
  });
  window.__diagPayload = payload;

  // Enviar a backend
  if(FORM_ENDPOINT){
    fetch(FORM_ENDPOINT, {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify(payload)
    })
    .then(function(response){ return response.json(); })
    .then(function(data){
      if(data.success){
        // Mostrar mensaje de confirmación
        document.getElementById("res-saludo").innerHTML =
          "<strong style='color:var(--success);font-size:1.3em;'>✅ ¡Cuestionario enviado exitosamente!</strong>" +
          "<p style='margin-top:15px;'>Revisa tu correo electrónico (<strong>" + payload.correo + "</strong>) " +
          "donde recibirás las próximas instrucciones y material del curso.</p>" +
          "<p style='background:var(--lightblue);padding:12px;border-radius:8px;margin-top:15px;'>" +
          "<i class='fas fa-envelope' style='color:var(--blue);margin-right:8px;'></i>" +
          "Si no ves el correo en tu bandeja principal, <strong>revisa tu carpeta de spam</strong>.</p>";

        // Ocultar tabla de resultados detallados
        document.getElementById("res-body").parentElement.style.display = "none";
        document.getElementById("res-reco").style.display = "none";
        document.getElementById("btn-download").style.display = "none";
        document.getElementById("res-score").parentElement.style.display = "none";
      }
    })
    .catch(function(error){
      alert("Hubo un error al enviar el cuestionario. Por favor intenta nuevamente.");
      console.error(error);
    });
  }

  document.getElementById("results").style.display = "block";
  document.getElementById("results").scrollIntoView({behavior:"smooth"});
});

document.getElementById("btn-download").addEventListener("click", function(){
  const p = window.__diagPayload; if(!p) return;
  let txt = "DIAGNÓSTICO — CURSO PROPEDÉUTICO DISDE (FCA-UAS)\n";
  txt += "Facilitador: Dr. Omar Valdez Palazuelos\n";
  txt += "Fecha: " + p.fecha + "\n\n";
  txt += "Nombre: " + p.nombre + "\nCorreo: " + p.correo + "\nGrado: " + p.grado +
         "\nCarrera (licenciatura): " + p.carrera_licenciatura +
         "\nÁrea de formación: " + p.area_formacion + "\nInstitución: " + (p.institucion||"-") + "\n\n";
  txt += "Conocimiento total: " + p.conocimiento_total + "/12\n\n";
  txt += "Resultados por área:\n";
  p.detalle.forEach(function(d){ txt += "- " + d.area + ": conocimiento " + d.conocimiento + " | autopercepción " + d.autopercepcion + "\n"; });
  const blob = new Blob([txt], {type:"text/plain;charset=utf-8"});
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "Diagnostico_DISDE_" + p.nombre.replace(/\s+/g,"_") + ".txt";
  a.click();
});
</script>
</body>
</html>
