
# 📅 Event Manager

[Event Manager](https://github.com/alexmihalascu/event_manager) is a web application designed to help users manage events, including creating, viewing, and editing events. Built with PHP and MySQL, it provides an intuitive interface for organizing and participating in events.

## 📋 Requirements

- **XAMPP** (with Apache and MySQL)
- **PHPMyAdmin** (for database management)

## 📸 Showcase

### Profile Editing
![Profile Editing](https://i.ibb.co/7yYkqvj/chrome-52b5lt-C3f-V.png)

### Events List
![Events List](https://i.ibb.co/2ZcGT4z/chrome-UTs-Mj-DTedv.png)

### Event Details with Comments
![Event Details](https://i.ibb.co/StjpwWy/chrome-T3-HATIr-Lr6.png)

### Welcome Screen
![Welcome Screen](https://i.ibb.co/HDd1Lm7/chrome-orf-ZMASDoj.png)

### Admin Event Controls
![Admin Event Controls](https://i.ibb.co/b6KwsGh/chrome-wdpek-XCS6j.png)

## 🚀 Setup

1. **Clone the Repository**

   Clone this repository to your local machine:
   ```bash
   git clone https://github.com/alexmihalascu/event_manager.git
   ```

2. **Move Project to XAMPP’s htdocs Folder**

   - Copy the project folder into the `htdocs` directory in your XAMPP installation:
     ```bash
     C:/xampp/htdocs/event_manager
     ```

3. **Start XAMPP**

   - Launch XAMPP and start the **Apache** and **MySQL** modules.

4. **Database Setup**

   - Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.
   - Create a new database.
   - Import the SQL file:
     - You can use either `event_manager.sql` or `eventdbv3.sql` found in the repository.
     - Select your database, click on `Import`, and upload the chosen SQL file.

5. **Access the Application**

   Open your browser and go to [http://localhost/event_manager](http://localhost/event_manager).

## 🛠️ Features

- **User Management**: Create, edit, and manage user profiles.
- **Event Creation and Management**: Add, edit, and delete events with details.
- **Event Viewing**: Browse and sort through events, view detailed event information.
- **Comments**: Add and view comments on event pages.
- **Admin Controls**: Mark top events, view attendees, and moderate events.

## 🤝 Contributions

Contributions are welcome! If you’d like to add features or improve functionality, feel free to fork the repository and submit a pull request.

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
