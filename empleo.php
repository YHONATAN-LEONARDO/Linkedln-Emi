<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleos</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:ital,wght@0,400;0,700;1,400;1,700&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Lobster&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Share+Tech&display=swap" rel="stylesheet">

</head>

<body>
    <?php include 'views/cabeza/header.php'; ?>

    <div class="filtros">
        <h2>Filtros de búsqueda</h2>

        <!-- Filtro por ubicación -->
        <label for="ubicacion">Ubicación:</label>
        <select id="ubicacion" name="ubicacion">
            <option value="">Todas</option>
            <option value="bolivia">Bolivia</option>
            <option value="peru">Perú</option>
            <option value="chile">Chile</option>
        </select>

        <!-- Filtro por tipo de jornada -->
        <fieldset>
            <legend>Jornada:</legend>
            <label><input type="checkbox" name="jornada" value="completa"> Completa</label>
            <label><input type="checkbox" name="jornada" value="media"> Media jornada</label>
            <label><input type="checkbox" name="jornada" value="remoto"> Remoto</label>
        </fieldset>

        <!-- Filtro por experiencia -->
        <label for="experiencia">Años de experiencia:</label>
        <input type="number" id="experiencia" name="experiencia" min="0" max="20" placeholder="0+">

        <!-- Botón para aplicar filtros -->
        <button class="btn-btn">Aplicar filtros</button>
    </div>

    <main class="empleo">
        <section class="empleo-izquierdo io">
            <div>
                <p>Principales Empleos que te recomienda la Emi</p>
                <p>En función de tu perfil, preferencias y actividad como solicitudes, búsquedas y contenido guardado.</p>
                <p>400 resultados</p>
            </div>
            <div class="plo">
                <img src="public/img/main.png" alt="">
                <div>
                    <p>Real Estate Rental Virtual AssistantReal Estate Rental Virtual Assistant</p>
                    <p>The Link Housing</p>
                    <p>Bolivia (remoto)</p>
                    <p class="btn-btn">Favorito</p>
                </div>
            </div>
        </section>
        <section class="empleo-derecho io">
            <input type="submit" value="Postularse" class="btn-btn">
            <p>Real Estate Rental Virtual Assistant</p>
            <p>Bolivia · hace 1 mes · Más de 100 solicitudes</p>
            <div>
                <p>En remoto</p>
                <p>Jornada Completa</p>
                <p>Solicitud sencilla</p>
            </div>
            <div>
                <p>Conoce al equipo de contratación</p>
                <div>
                    <img src="public/img/image.png" alt="">
                    <div>
                        <p>Jeremy Garcia</p>
                        <p>Real Estate Investor and Housing Solutions</p>
                        <p>Anunciante del empleo</p>
                    </div>
                </div>
            </div>
            <p>Acerca del empleo</p>
            <p>About Us: The Link Housing is a dynamic and rapidly growing real estate company dedicated to providing exceptional rental properties to our clients. We pride ourselves on our commitment to excellence and our passion for helping individuals and families find their perfect home.


                Job Description:
                We are seeking a highly motivated and detail-oriented Virtual Assistant to join our team. As a Virtual Assistant, you will play a key role in our real estate operations by assisting with the acquisition of homes for rent. This is a remote position with flexible hours within the Eastern Time Zone.


                Responsibilities:


                Make phone calls to potential homeowners to inquire about listing their properties for rent.
                Gather and organize relevant information using Google Sheets and Monday.com.
                Assist with administrative tasks and documentation related to property acquisition.
                Provide excellent customer service to homeowners and potential clients.


                Key Responsibilities:


                Source suitable housing options within a 10-mile radius of the displaced residence using platforms like Airbnb, Zillow, and Furnished Finder.
                Utilize Google Maps to center searches on target locations, ensuring proximity, quality, and availability.
                Conduct outbound calls to property owners and landlords to negotiate rental agreements and advocate for displaced families.
                Submit accurate, comprehensive property listings for internal review, including pricing, amenities, location, and owner contact information.
                Maintain a high standard of professionalism in written and spoken English when communicating with landlords and internal teams.
                Accurately log and track submissions and activities using Google Sheets, Google Docs, and Monday.com.
                Work efficiently in a fast-paced environment, balancing speed, accuracy, and customer satisfaction.
                Apply negotiation and sales skills to encourage property owners to list their homes for temporary housing.


                Key Skills & Requirements:
                Proficiency with Google Workspace (Docs, Sheets, Maps)
                Strong experience using OTA (Online Travel Agency) platforms: Airbnb, Furnished Finder, Zillow
                Excellent understanding of geolocation and map-based search techniques
                Strong communication skills – both verbal and written (professional English)
                Ability to work independently and manage time across multiple cases
                Knowledge of Monday.com or similar project management tools
                Sales and negotiation experience is essential
                Must be available to work Pacific Standard Time (PST) hours


                Ideal Candidate:


                You are tech-savvy, detail-focused, and mission-driven. You’re passionate about helping families in need, and you thrive in a remote, fast-paced environment where accuracy, speed, and empathy matter. You’re also proactive, highly organized, and confident in negotiating terms with landlords.


                Requirements:
                Strong English communication skills and comfortable making phone calls.
                Proficiency in Google Sheets or similar spreadsheet software.
                Detail-oriented with excellent organizational skills.
                Ability to work independently and prioritize tasks effectively.</p>

        </section>

    </main>
    <?php include 'views/cabeza/footer.php'; ?>
</body>

</html>