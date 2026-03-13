<p align="center">

# BITHAVEN Smart Locker System

### A Safe Haven for Belongings, Tied to Computer Bits

Secure • Automated • Smart Locker Infrastructure

</p>

<p align="center">

<img src="https://img.shields.io/badge/Smart%20System-IoT-blue?style=for-the-badge">
<img src="https://img.shields.io/badge/Backend-Laravel-red?style=for-the-badge">
<img src="https://img.shields.io/badge/Frontend-Vue.js-42b883?style=for-the-badge">
<img src="https://img.shields.io/badge/Hardware-Python-blue?style=for-the-badge">
<img src="https://img.shields.io/badge/Database-MySQL-orange?style=for-the-badge">

</p>

---

# BITHAVEN Smart Locker System

BITHAVEN is a **smart locker infrastructure designed for the Computer Engineering Department of Mariano Marcos State University**. The system provides a secure and automated storage solution for students, faculty, and visitors who need a safe place to store personal belongings while attending classes, events, or activities.

Traditional lockers rely on keys and manual management, which often leads to lost keys, unauthorized access, and poor monitoring. BITHAVEN replaces these outdated methods with a **fully digital locker management system** powered by RFID authentication, automated locker control, and a centralized monitoring platform.

Beyond security and convenience, BITHAVEN also serves as a **departmental income generating system**, allowing lockers to be rented through a coin-based kiosk interface.

The result is a **modern smart campus solution** that improves security, efficiency, and technology adoption within the department.

---

# Core Features

## Smart RFID Authentication

Users authenticate themselves using RFID cards. This eliminates the need for physical locker keys and ensures that only authorized users can access locker services.

## Automated Locker Access

Once a locker is rented, the system automatically unlocks the assigned locker through relay-based hardware control. This process removes the need for manual supervision.

## Coin-Based Rental System

The system includes a coin-operated payment mechanism that allows users to rent lockers for a specific duration. This feature supports the department’s goal of creating an **Income Generating Project (IGP)**.

## Real-Time Locker Monitoring

Locker availability, rental status, and operational conditions are tracked in real time. This allows administrators to easily monitor the system and ensure proper usage.

## Administrative Control Dashboard

Authorized administrators have access to a web-based dashboard where they can monitor locker activity, manage rentals, override locker access when necessary, and generate system reports.

## Penalty and Rental Management

The system automatically tracks rental durations and can apply penalties when lockers are not retrieved within the allocated time.

---

# Security and Reliability

Security is one of the primary design goals of BITHAVEN.

The system implements several layers of protection to ensure that locker access and system operations remain controlled and secure.

RFID authentication ensures that locker services are only accessible to verified users. Each interaction between the kiosk and backend server is validated through backend authorization before any locker operation is executed.

Hardware actions such as unlocking lockers are performed only after receiving a valid unlock authorization from the backend system. These unlock requests are handled through a controlled hardware daemon that executes commands securely.

Administrative access to system controls is restricted through role-based permissions, ensuring that only authorized personnel can perform sensitive operations such as forcing locker unlocks or clearing penalties.

Through these mechanisms, BITHAVEN provides a secure environment for storing personal belongings while maintaining full system accountability.

---

# How the System Operates

BITHAVEN combines multiple technologies to deliver a seamless locker rental experience.

When a user approaches the kiosk, they authenticate using their RFID card. Once the system verifies the user, the kiosk interface allows them to choose an available locker. After selecting a locker, the user inserts coins to activate the rental session.

The kiosk communicates with the backend server, which records the rental session and authorizes the locker to unlock. A hardware daemon running on the kiosk device listens for authorized unlock jobs from the backend server and activates the relay controller responsible for opening the locker door.

During the rental period, the system monitors locker status and rental duration. If the rental period expires without the locker being retrieved, a penalty may be applied. Administrators can monitor all system activity through the administrative dashboard.

---

# System Architecture

BITHAVEN system follows a layered architecture composed of four major operational layers.

The **user interaction layer** consists of the kiosk interface where users authenticate and interact with the locker system. This interface is built using modern web technologies and runs on the kiosk device.

The **application layer** contains the backend server that manages all core system operations. This includes authentication, locker rental sessions, payment processing, penalty management, and administrative functions.

The **data layer** stores all system records, including student information, rental sessions, locker states, and financial transactions. This ensures that all system activity can be tracked and analyzed.

Finally, the **hardware control layer** connects the software system with the physical lockers. A Python-based daemon communicates with the backend server and activates relay controllers that unlock lockers when authorized.

This architecture allows BITHAVEN to seamlessly connect software services with physical hardware, creating a complete smart locker ecosystem.

---

# Technology Stack

### Backend System

Laravel is used as the primary backend framework to manage system logic, APIs, authentication, and administrative operations.

### Frontend Interfaces

The kiosk interface and administrative dashboard are built using Vue.js, providing responsive and modern user interfaces.

### Hardware Integration

Python is used to implement the hardware daemon responsible for communicating with relay controllers and executing locker unlock operations.

### Database

MySQL stores system data including user information, rental records, locker status, payment sessions, and system logs.

---

# Author

**Ace Argee F. Vizcarra**  
Computer Engineering  
Mariano Marcos State University

LinkedIn:  
https://www.linkedin.com/in/argee-vizcarra-a99b47317/

---
