<?php
if (isAdmin()) {
    temp("danger", "Only guest and members can access this page");
    return redirect("/");
}
?>
<style>
    body {
        background-color: #e3e3e3;
    }

    .location-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        box-sizing: border-box;
        margin: 0 0;
        width: 100%;
    }

    .left-content {
        padding-right: 50px;
        width: 40%;
    }

    .left-content h2 {
        font-size: 3rem;
        color: #092c58;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .left-content h2 span {
        text-decoration: underline;
        text-decoration-color: #f7984f;
    }

    .left-content .description {
        font-size: 1rem;
        color: #333;
        margin-bottom: 30px;
    }

    .find-office-btn {
        display: inline-block;
        background-color: #092c58;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 1.1rem;
        transition: background-color 0.3s ease;
    }

    .find-office-btn:hover {
        background-color: #f7984f;
        color: white;
    }

    .right-map {
        flex: 1.2;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 30px 0 rgba(0, 0, 0, 0.19);
    }

    .right-map .map-image {
        max-width: 100%;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .view-map-btn {
        background-color: #4caf50;
        color: #fff;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 20px;
        transition: background-color 0.3s;
    }

    .view-map-btn:hover {
        background-color: #45a049;
    }

    .viewMap-container {
        width: 80%;
        display: flex;
        justify-content: end;
        align-items: center;
    }

    .contactus-container {
        margin: 30px;
        display: flex;
        justify-content: space-between;
        padding: 40px;
        background-color: #f4f4f4;
    }

    .contact-info-container,
    .contact-form {
        width: 45%;
        background: white;
        padding: 30px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .contact-info-container h3,
    .contact-form h3 {
        margin-bottom: 20px;
        color: #333;
    }

    .contact-info-container p {
        margin: 10px 0;
        line-height: 1.6;
    }

    .contact-info-container p i {
        color: orange;
        margin-right: 10px;
    }

    .contact-info{
        margin: 30px;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 15px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .contact-form input[type="submit"] {
        background-color: #53b94d;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .contact-form input[type="submit"]:hover {
        background-color: darkorange;
    }

    .small-text {
        font-size: 12px;
        color: #aaa;
        margin-top: 20px;
    }
</style>

<body>
    <div class="location-section">
        <div class="left-content">
            <h2 class="title">Explore Our <br><span>Locations</span></h2>
            <p class="description">
                Discover premium furniture showrooms near you. Use our interactive map to find the nearest location for exclusive collections and expert design services.
            </p>
            <a href="https://www.google.com/maps/place/Tunku+Abdul+Rahman+University+of+Management+and+Technology+(TAR+UMT)/@3.215255,101.726557,14z/data=!4m6!3m5!1s0x31cc3843bfb6a031:0x2dc5e067aae3ab84!8m2!3d3.2152552!4d101.7265571!16s%2Fm%2F025z3fz?hl=en&entry=ttu&g_ep=EgoyMDI0MDkxOC4xIKXMDSoASAFQAw%3D%3D" class="find-office-btn">Find Our Office</a>
        </div>

        <div class="right-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15934.1327910035!2d101.71968314735291!3d3.2164364481390497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc3843bfb6a031%3A0x2dc5e067aae3ab84!2sTunku%20Abdul%20Rahman%20University%20of%20Management%20and%20Technology%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1726851585980!5m2!1sen!2smy" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <div class="viewMap-container">
        <a href="https://www.google.com/maps/place/Tunku+Abdul+Rahman+University+of+Management+and+Technology+(TAR+UMT)/@3.215255,101.726557,14z/data=!4m6!3m5!1s0x31cc3843bfb6a031:0x2dc5e067aae3ab84!8m2!3d3.2152552!4d101.7265571!16s%2Fm%2F025z3fz?hl=en&entry=ttu&g_ep=EgoyMDI0MDkxOC4xIKXMDSoASAFQAw%3D%3D" class="view-map-btn">View on google map</a>
    </div>

    <div class="contactus-container">
        <div class="contact-info-container">
            <h3>CONTACT US</h3>
            <div class="contact-info">
                <strong><p style="text-decoration: underline;"><i class="fas fa-phone"></i> CALL US</p></strong>
                <p>Tel : 012-3456691</p>
            </div>

            <div class="contact-info">
                <strong><p style="text-decoration: underline;"><i class="fas fa-map-marker-alt"></i> LOCATION</p></strong>
                <p>Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang, Setapak, 53300 Kuala Lumpur, Federal Territory of Kuala Lumpur</p>
            </div>
            <div class="contact-info">
            <strong><p style="text-decoration: underline;"><i class="fas fa-clock"></i> BUSINESS HOURS</p></strong>
                <p>Mon – Fri: 10 am – 8 pm, Sat, Sun: Closed</p>
            </div>
            </div>
    </div>
</body>