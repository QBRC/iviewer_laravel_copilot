# iViewer Frontend Setup

In order to use the iViewer frontend on your localhost or server, you may need to edit the following files after cloning the repository:

## Configuration Files

### 1. The Main `.env` File

Location: Root of the downloaded folder (`iviewer-laravel-copilot`).

- **Ollama Host:** If you decide to build your own Ollama, specify the Ollama host IP and ports.
- **OpenAI API Key:** If you want to try with GPT, you can just put your OpenAI API key here.
  - The default Laravel website will use Ollama, but you can change the API on the website to invoke GPT-4 instead.
- **Database Configuration:** 
  - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: These are used to create the database in a container.

### 2. The Laravel `.env` File

Location: Inside the `laravel_smp` folder.

- **Image Path:** 
  - Edit `# Image_PATH`. If you want the website to be accessible beyond localhost, change `localhost` to your IP address.
- **Website Port:** 
  - If you have other services running on port 80, assign another port but ensure this port matches the port configured in the Nginx file (`nginx/iviewer.config` line 2 `listen port;`).
- **API Configuration:**
  - Edit `# API` to change the `API_IP` or service port. Ensure the port matches the one specified in each Dockerfile in the folders: `deepzoom`, `annotation`, `nuclei`, `copilot`.
- **Database Configuration:**
  - If you already have MySQL running on your localhost on port 3307, you may want to run your DB container on another port (you can change the port configuration in `db/my.cnf`). Ensure the port specified here matches the one in `db/my.cnf` so the Laravel website can find the MySQL service. Also, make sure the `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` are the same as in the main `.env` file to load and use the database.

### 3. MySQL Configuration File

Location: `db/my.cnf`

- Used to change the database port.

### 4. Nginx Configuration File

Location: `nginx/iviewer.config`

- Used to change the website port.

## Steps to Run the Docker Compose

1. Navigate to the `iviewer-laravel-copilot` folder:
   ```sh
   cd iviewer-laravel-copilot
   ```

2. Edit the two .env files as described above.
3. Start the Docker containers:
   ```sh
   docker compose up -d
   ```	
4. Run the initialization script to generate the Laravel key and load default data such as the login user and default images:
   ```sh
   ./cmd.sh
   ```	
5. Access the Application
   Open your browser and go to http://localhost:port or http://ip:port.

## How to Display Your Own Slides

1. Navigate to the images directory:
   ```sh
   cd iviewer-laravel-copilot/laravel_smp/public/images/original/
   ```	
2. Create a new project folder:
   ```sh
   mkdir projectname
   ```	
3. Create a batch folder within your project folder:
   ```sh
   cd projectname
   mkdir batchname
   ```	
4. Place your slides in the batchname folder.
5. Open the website and select "Import" from the left menu.
6. Download the input template file from step 2 link.
7. Fill out the required columns:
- `uuid` (make it unique)
- `sys_image_file_name` (e.g., `example.svs`)
- `project_codename` (same as the project folder you created)
- `dataset_codename` (same as the batch folder you created)
8. Upload the filled template through the website.
9. Review your input, then select the team you want the members to view. You can create your own teams through the website by clicking "Teams" in the left menu.
10. Click "Submit".
11. Click "Images" in the left menu, then select the project name and batch name to view your uploaded slides.

## Chat API Notice
  After your containers are running, it may take some time for the chat API to become available. If you encounter a 404 error, please close it and try again later.