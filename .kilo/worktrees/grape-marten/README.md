Nexar DMS – File Upload Microservice

This project is a dedicated microservice responsible for handling secure file uploads to cloud storage.
It is part of a larger Document Management System (DMS) ecosystem, where multiple services collaborate to offer a complete digital document workflow.

Overview

The File Upload Microservice provides:

Secure upload of documents to cloud storage (e.g., AWS S3, Google Cloud, DigitalOcean Spaces, etc.)

APIs for managing user and directory permissions

API endpoints for service-to-service communication

Folder-based access control

Metadata tracking for uploaded documents

Integration-friendly JSON responses

This microservice works alongside other services (e.g., document processing, OCR, workflow automation) to form a fully modular DMS platform.

Features

REST API for file uploads

Cloud storage integration

Directory and folder permission control

User-based access restrictions

Microservice-ready architecture

Built using Laravel

    Architecture Overview

This service is designed to be:

Independent — each microservice owns its scope

Communicative — lightweight JSON APIs

Scalable — can run alone or in a cluster

Secure — permissions + validation + integration-ready

🛠️ Tech Stack

Laravel 11

PHP 8.2+

PostgreSQL

Cloud Storage (configurable)

Docker (optional)

API Endpoints (Short Overview)
Endpoint	Method	Description
/api/upload	POST	Upload a file
/api/directories	GET	List directories
/api/directories	POST	Create new directory
/api/permissions	POST	Assign folder permissions