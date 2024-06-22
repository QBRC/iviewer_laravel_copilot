# I-Viewer Copilot Backend
## Introduction
I-viewer copilot is a comprehensive online framework designed to address the needs of collaborative pathology analysis while harnessing the power of AI. I-viewer deployed advanced real-time AI-agents for different tasks and relies on MLLM for information integration and Human-AI collaboration. 

# iViewer Frontend Setup

In order to use the iViewer frontend on your localhost or server, you may need to edit the following files after cloning the repository:

## Configuration Files

Before editing those files, you need to change the ownership of the laravel_smp folder to the user with ID 1004.
```
chown -R 1004 laravel_smp
```

### 1. The Main `.env` File

Location: Root of the downloaded folder (`iviewer-copilot`).

- **Ollama Host:** If you decide to build your own Ollama, specify the Ollama host IP and ports. we recommend to host ollama service on a separate server. (Instructions about how to set up ollama and LLM models can be found here: https://github.com/ollama/ollama)
- **OpenAI API Key:** If you want to try with GPT, you can just put your OpenAI API key here.
  - The default Laravel website will use Ollama, but you can change the API on the website to invoke GPT-4 instead.
- **Database Configuration:** 
  - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: These are used to create the database in a container.

### 2. The Laravel `.env` File

Location: Inside the `laravel_smp` folder.

- **Image Path:** 
  - Edit `# Image_PATH`. If you want the website to be accessible beyond localhost, change `localhost` to your IP address. If you have other services running on port 80, assign another port but ensure this port matches the port configured in the Nginx file (`nginx/iviewer.config` line 2 `listen port;`). 
  ```
  IMAGE_PATH=http://localhost:5000/images/
  ```
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

1. Navigate to the `iviewer-copilot` folder:
   ```sh
   cd iviewer-copilot
   ```

2. Edit the two .env files, mysql configure file and nginx configure file as described above.
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
   Or view a demo by opening the `./templates/index.html`

6. logging into iViewer
   Default login credentials:
   Admin login:
      - Username: admin@email.com
      - Password: admin

   Admin privileges include access to additional options in the menu:
      - Add/Edit Users
      - Create/Edit Teams
      - Change API Links (Models)
      - Upload New Slides CSV Files

   Regular user login:
      - Username: user1@email.com
      - Password: helloworld
  
   Regular users can only view images.

## How to Display Your Own Slides

1. Navigate to the images directory:
   ```sh
   cd iviewer-copilot/laravel_smp/public/images/original/
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

## Extend I-Viewer with customer analysis pipeline
User can add their own pipeline into I-Viewer with `offline` interface and `online` interface. Basically it takes three steps:
```
## Create a generator agent
class GeneratorAgent:
    def prepare_inputs(self, requests):
        request_params = decode(requests)
        boxes = get_bbox(request_params)
        roi_image = get_roi_tile(request_params)
        ...
        
        return {'roi_image': roi_image, 'boxes': boxes, ...}
    
    def analysis_offline(self, inputs):
        serialized_item = serialize(inputs)
        await redis_client.stream_push(registry, serialized_item)
    
    def analysis_online(self, inputs):
        outputs = agents.analysis_online(inputs)
        return Response(outputs)

## Create a analysis agent
class AnalysisAgent:
    def predict(self, inputs):
        return pipeline(inputs)
    
    def analysis_offline(self):
        serialized_item = redis_client.stream_fetch(registry)
        inputs = deserilize(serialized_item)
        outputs = self.predict(inputs)
        export_to_db(postprocess(outputs))

    def analysis_online(self, inputs):
        outputs = self.predict(inputs)
        return postprocess(outputs)

## Register model and generator
MODEL_REGISTRY = ModelRegistry()
MODEL_REGISTRY.register("registry_name", "model", AnalysisAgent)
MODEL_REGISTRY.register("registry_name", "generator", GeneratorAgent)
```
