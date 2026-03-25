<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sidenav shadow-right sidenav-light">
            <div class="sidenav-menu">
                <div class="nav accordion" id="accordionSidenav">
                    
                    <!-- =========================================== -->
                    <!-- TANYA APPS — menu baru                      -->
                    <!-- =========================================== -->
                    <div class="sidenav-menu-heading"
                         data-aos="fade-left"
                         data-aos-delay="420">Assistant</div>

                    <a class="nav-link spa-link" href="#" data-page="tanya_apps"
                       data-aos="fade-left"
                       data-aos-delay="440"
                       style="position:relative;">
                        <div class="nav-link-icon"><i data-feather="message-circle"></i></div>
                        Assistant
                        <!-- Badge "New" kecil -->
                        <span class="badge bg-primary ms-auto"
                              style="font-size:.65rem; padding:2px 7px; border-radius:20px;">
                            New
                        </span>
                    </a>
                    <!-- =========================================== -->


                    <!-- INVENTORY -->
                    <div class="sidenav-menu-heading">Delivery Management</div>

                    <!-- Dashboard -->
                    <a class="nav-link active spa-link" href="#" data-page="dashboard">
                        <div class="nav-link-icon"><i data-feather="activity"></i></div>
                        Dashboard
                    </a>

                    <div class="sidenav-menu-heading">Main Menu</div>

                    <!-- WH Management -->
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseFlows" aria-expanded="false" aria-controls="collapseFlows">
                        <div class="nav-link-icon"><i data-feather="repeat"></i></div>
                        OrderManagement
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="collapseFlows" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="#" data-page="order_request">
                                <div class="nav-link-icon"><i data-feather="clipboard"></i></div>
                                Order Req List
                            </a>
                            <a class="nav-link spa-link" href="#" data-page="handoverdone">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Order Req Done
                            </a>
                            <a class="nav-link spa-link" href="#" data-page="backtopool">
                                <div class="nav-link-icon"><i data-feather="rotate-cw"></i></div>
                                Order Req BTP
                            </a>
                            <a class="nav-link spa-link" href="#" data-page="keep_at_pool">
                                <div class="nav-link-icon"><i data-feather="bookmark"></i></div>
                                Pool Mover
                            </a>
                            <a class="nav-link spa-link" href="#" data-page="orderReqTracking">
                                <div class="nav-link-icon"><i data-feather="map-pin"></i></div>
                                Order Req Detail
                            </a>
                        </nav>
                    </div>

                    <div class="sidenav-menu-heading">Billing Report</div>

                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseBillingReport" aria-expanded="false"
                        aria-controls="collapseBillingReport">
                        <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                        Billing Report
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="collapseBillingReport" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">

                            <a class="nav-link spa-link" href="#" data-page="DashboardBilling">
                                <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                                Dashboard
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="waiting_upload_scpod">
                                <div class="nav-link-icon"><i data-feather="upload"></i></div>
                                Waiting Upload SCPOD
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="waiting_approved_scpod">
                                <div class="nav-link-icon"><i data-feather="check-square"></i></div>
                                Waiting APP SCPOD
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="waiting_submit_pi">
                                <div class="nav-link-icon"><i data-feather="send"></i></div>
                                Waiting Submit PI
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="waiting_approved_pi">
                                <div class="nav-link-icon"><i data-feather="clock"></i></div>
                                Waiting APP PI
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="waiting_submit_invoice">
                                <div class="nav-link-icon"><i data-feather="file-plus"></i></div>
                                Waiting Submit INV
                            </a>

                            <a class="nav-link spa-link" href="#" data-page="invoicedone">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Invoice Done
                            </a>

                        </nav>
                    </div>


                    <!-- SETTINGS -->
                    <div class="sidenav-menu-heading">Settings</div>
                    <?php
          
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    ?>
                    <!-- User Management -->
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseComponents" aria-expanded="false" aria-controls="collapseComponents">
                        <div class="nav-link-icon"><i data-feather="users"></i></div>
                        User Management
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="collapseComponents" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link spa-link" href="#" data-page="UserSetting">
                                <div class="nav-link-icon"><i data-feather="user"></i></div>
                                User Setting
                            </a>
                        </nav>
                    </div>
                    <?php
                    }
                    ?>



                    <?php
          
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    ?>
                    
                    <div class="sidenav-menu-heading">Settings</div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapsesettings" aria-expanded="false" aria-controls="collapsesettings">
                        <div class="nav-link-icon"><i data-feather="settings"></i></div>
                        Settings
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="collapsesettings" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                        <a class="nav-link spa-link" href="#" data-page="AddDriver">
                            <div class="nav-link-icon"><i data-feather="plus"></i></div>
                            Register Driver
                        </a>
                        <a class="nav-link spa-link" href="#" data-page="MasterOrigin">
                            <div class="nav-link-icon"><i data-feather="plus"></i></div>
                            Add Origin
                        </a>
                        <a class="nav-link spa-link" href="#" data-page="MasterDestination">
                            <div class="nav-link-icon"><i data-feather="plus"></i></div>
                            Add Destination
                        </a>
                        <a class="nav-link spa-link" href="#" data-page="MasterMOT">
                            <div class="nav-link-icon"><i data-feather="plus"></i></div>
                            Create MOT
                        </a>
                        <a class="nav-link spa-link" href="#" data-page="Add_Status_Delivery">
                            <div class="nav-link-icon"><i data-feather="plus"></i></div>
                            Add Status Delivery
                        </a>
                        </nav>
                    </div>
                    
                    <!--<div class="sidenav-menu-heading">Resources</div>-->

                    <!-- Email Recipient Settings -->
                    <!--<a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"-->
                    <!--    data-bs-target="#collapseEmailRecipients" aria-expanded="false"-->
                    <!--    aria-controls="collapseEmailRecipients">-->
                    <!--    <div class="nav-link-icon"><i data-feather="mail"></i></div>-->
                    <!--    Email Recipients-->
                    <!--    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>-->
                    <!--</a>-->

                    <!--<div class="collapse" id="collapseEmailRecipients" data-bs-parent="#accordionSidenav">-->
                    <!--    <nav class="sidenav-menu-nested nav">-->
                    <!--        <a class="nav-link spa-link" href="#" data-page="AddRecipientMail">-->
                    <!--            <div class="nav-link-icon"><i data-feather="plus-circle"></i></div>-->
                    <!--            Manage Recipients-->
                    <!--        </a>-->
                    <!--        <a class="nav-link spa-link" href="#" data-page="NotificationMail">-->
                    <!--            <div class="nav-link-icon"><i data-feather="bell"></i></div>-->
                    <!--            Notification Mail-->
                    <!--        </a>-->
                    <!--    </nav>-->
                    <!--</div>-->
                    <?php
                    }
                    ?>
                </div>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">