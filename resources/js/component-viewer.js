import * as THREE from 'https://esm.sh/three@0.155.0';
import { OrbitControls } from 'https://esm.sh/three@0.155.0/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';
import interact from 'https://esm.sh/interactjs@1.10.17';


let scene, camera, renderer, controls;
let caseModel = null;
let moboModel = null;
let cpuModel = null;
let psuModel = null;
let coolerModel = null;
let ssdModel = null;
let gpuModel = null;
// let ramModel = null;
let ramModels = [];
let moboSlotPosition = null;
let cpuSlotPosition = null;
let psuSlotPosition = null;
let coolerSlotPosition = null;
let ssdSlotPosition = null;
let gpuSlotPosition = null;
let ramSlotPosition = null;
let selectedCaseModelUrl = null;
let selectedMoboModelUrl = null;
let selectedCpuModelUrl = null;
let selectedPsuModelUrl = null;
let selectedCoolerModelUrl = null;
let selectedSsdModelUrl = null;
let selectedGpuModelUrl = null;
let selectedRamModelUrl = null;

function setupCatalogClickHandlers() {
  document.querySelectorAll('.catalog-item').forEach(item => {
    item.addEventListener('click', async () => {
      const modelUrl = item.getAttribute('data-model');
      const type = item.getAttribute('data-type');

      if (!modelUrl) {
        alert('Model not available for this component.');
        return;
      }

      if (type === 'case') {
        selectedCaseModelUrl = modelUrl; // Save selected model
        console.log('Selected model URL for dragging:', selectedCaseModelUrl);

        // SPAWNS CASE IN THE SCENE
        spawnCase(new THREE.Vector3(0,0,0), selectedCaseModelUrl);
      } else if (type === 'motherboard') {
        selectedMoboModelUrl = modelUrl;
        console.log('Selected model URL for dragging:', selectedMoboModelUrl);
      } else if (type === 'cpu') {
        selectedCpuModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedCpuModelUrl);
      } else if (type === 'psu') {
        selectedPsuModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedPsuModelUrl);
      } else if (type === 'cooler') {
        selectedCoolerModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedCoolerModelUrl);
      } else if (type === 'ssd') {
        selectedSsdModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedSsdModelUrl);
      } else if (type === 'gpu') {
        selectedGpuModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedGpuModelUrl);
      } else if (type === 'ram') {
        selectedRamModelUrl = modelUrl;
        console.log('Selected model URL for draggin:', selectedRamModelUrl);
      } 

    })
  })
}

init();
setupCatalogClickHandlers();
animate();

function init() {
  scene = new THREE.Scene();
  // scene.background = new THREE.Color('white');

  const container = document.getElementById('canvas-container');
  const width = container.clientWidth;
  const height = container.clientHeight;

  camera = new THREE.PerspectiveCamera(30, width/height, 0.1, 1000);
  camera.position.set(20, 0, 0);

  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(width, height);
  renderer.outputEncoding = THREE.sRGBEncoding;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.2;
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  container.appendChild(renderer.domElement);


  controls = new OrbitControls(camera, renderer.domElement);

  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5); 
  scene.add(ambientLight);

  const directionalLight = new THREE.DirectionalLight(0xffffff, 7.0); // LIGHT SETTINGS HITTING THE MODEL
  directionalLight.position.set(2, 10, 5);
  scene.add(directionalLight);

  // RESIZE LISTENER USING CONTAINER SIZE
  window.addEventListener('resize', () => {
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
  });

  setupDragAndDrop();
}

function animate() {
  requestAnimationFrame(animate);
  renderer.render(scene, camera);
}

async function loadGLTFModel(url) {
  const loader = new GLTFLoader();
  const gltf = await loader.loadAsync(url);
  return gltf.scene;
}


function setupDragAndDrop() {
  let draggingId = null;
  let draggingEl = null;
  let originalSlotMaterial = null;
  let mobomarker = null;
  let casemarker = null;
  let cpumarker = null;
  let psumarker = null;
  let coolermarker = null;
  let ssdmarker = null;
  let gpumarker = null;
  let rammarker = null;
  let wasDroppedSuccessfully = false; // Track if the drop was successful

  interact('.draggable').draggable({
    listeners: {
      start(event) {
        draggingId = event.target.id;
        draggingEl = event.target;
        draggingEl.style.opacity = '0.5';

        // Change the cursor to grabbing when drag starts
        document.body.style.cursor = 'grabbing'; 

        // If dragging Mobo, highlight the Mobo slot
        if (draggingId === 'motherboard' && caseModel) {
          const moboSlot = caseModel.getObjectByName('Slot_Mobo');
          if (moboSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = moboSlot.material; // Store the original material
            moboSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            mobomarker = new THREE.Mesh(
              new THREE.BoxGeometry(2, 2, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            mobomarker.rotation.x = 0; // No rotation on the Y axis
            mobomarker.rotation.y = Math.PI / 2;  // 90 degrees        
            mobomarker.rotation.z = 0;          // No rotation on the Z axis
            mobomarker.position.set(moboSlotPosition.x, moboSlotPosition.y - 0.9, moboSlotPosition.z + -0.61); // Position the mobomarker
            scene.add(mobomarker);
          }
        } 

        // If dragging psu, highlight the psu slot
        if (draggingId === 'psu' && caseModel) {
          const psuSlot = caseModel.getObjectByName('Slot_Psu');
          if (psuSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = psuSlot.material; // Store the original material
            psuSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            psumarker = new THREE.Mesh(
              new THREE.BoxGeometry(2, 1.02, 1.8),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            psumarker.rotation.x = 0; // No rotation on the Y axis
            psumarker.rotation.y = Math.PI / 2;  // 90 degrees        
            psumarker.rotation.z = 0;          // No rotation on the Z axis
            psumarker.position.set(psuSlotPosition.x + 0.79, psuSlotPosition.y - .4, psuSlotPosition.z - 1); // Position the psumarker
            scene.add(psumarker);
          }
        } 

        // If dragging cpu, highlight the cpu slot
        if (draggingId === 'cpu' && moboModel) {
          const cpuSlot = moboModel.getObjectByName('Slot_Cpu');
          if (cpuSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = cpuSlot.material; // Store the original material
            cpuSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            cpumarker = new THREE.Mesh(
              new THREE.BoxGeometry(0.8, 0.8, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            cpumarker.rotation.x = 0; // No rotation on the Y axis
            cpumarker.rotation.y = Math.PI / 2;  // 90 degrees        
            cpumarker.rotation.z = 0;          // No rotation on the Z axis
            cpumarker.position.set(cpuSlotPosition.x, cpuSlotPosition.y + .2, cpuSlotPosition.z - 0.07); // Position the cpumarker
            scene.add(cpumarker);
          }
          
          
        }

        // If dragging cooler, highlight the cooler slot
        if (draggingId === 'cooler' && moboModel) {
          const coolerSlot = moboModel.getObjectByName('Slot_Cooler');
          if (coolerSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = coolerSlot.material; // Store the original material
            coolerSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            coolermarker = new THREE.Mesh(
              new THREE.BoxGeometry(0.8, 0.8, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            coolermarker.rotation.x = 0; // No rotation on the Y axis
            coolermarker.rotation.y = Math.PI / 2;  // 90 degrees        
            coolermarker.rotation.z = 0;          // No rotation on the Z axis
            coolermarker.position.set(coolerSlotPosition.x, coolerSlotPosition.y, coolerSlotPosition.z - 0.02); // Position the coolermarker
            scene.add(coolermarker);
          }
          
        }

        // If dragging ssd, highlight the ssd slot
        if (draggingId === 'ssd' && moboModel) {
          const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
          if (ssdSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = ssdSlot.material; // Store the original material
            ssdSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            ssdmarker = new THREE.Mesh(
              new THREE.BoxGeometry(0.8, 0.2, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            ssdmarker.rotation.x = 0; // No rotation on the Y axis
            ssdmarker.rotation.y = Math.PI / 2;  // 90 degrees        
            ssdmarker.rotation.z = 0;          // No rotation on the Z axis
            ssdmarker.position.set(ssdSlotPosition.x, ssdSlotPosition.y + 0.11, ssdSlotPosition.z + 0.4); // Position the ssdmarker
            scene.add(ssdmarker);
          }
          
        }

        // If dragging gpu, highlight the gpu slot
        if (draggingId === 'gpu' && moboModel) {
          const gpuSlot = moboModel.getObjectByName('Slot_Gpu');
          if (gpuSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = gpuSlot.material; // Store the original material
            gpuSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            gpumarker = new THREE.Mesh(
              new THREE.BoxGeometry(2, 0.5, 1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            gpumarker.rotation.x = 0; // No rotation on the Y axis
            gpumarker.rotation.y = Math.PI / 2;  // 90 degrees        
            gpumarker.rotation.z = 0;          // No rotation on the Z axis
            gpumarker.position.set(ssdSlotPosition.x + 0.5, ssdSlotPosition.y - 0.3, ssdSlotPosition.z + 0.6); // Position the gpumarker
            scene.add(gpumarker);
          }
          
        }

        // If dragging ram, highlight the ram slot
        if (draggingId === 'ram' && moboModel) {
          // Look for the individual RAM slots instead of a single 'Slot_Ram'
          const ramSlot01 = moboModel.getObjectByName('Slot_Ram1');
          const ramSlot02 = moboModel.getObjectByName('Slot_Ram2');
          
          if (ramSlot01 || ramSlot02) {
            // Save the original material and change to the highlighted one
            // We'll highlight the first found slot, or you can highlight both
            const firstRamSlot = ramSlot01 || ramSlot02;
            originalSlotMaterial = firstRamSlot.material; // Store the original material
            firstRamSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            rammarker = new THREE.Mesh(
              new THREE.BoxGeometry(0.1, 1.6, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            // Rotate 45 degrees on the X axis
            rammarker.rotation.x = 0; // No rotation on the Y axis
            rammarker.rotation.y = Math.PI / 2;  // 90 degrees        
            rammarker.rotation.z = 0;          // No rotation on the Z axis
            
            // Use the position of the first RAM slot for the marker
            const ramSlotPosition = new THREE.Vector3();
            firstRamSlot.getWorldPosition(ramSlotPosition);
            rammarker.position.set(ramSlotPosition.x, ramSlotPosition.y + 0.04, ramSlotPosition.z - 0.01); // Position the rammarker
            scene.add(rammarker);
          }
        }
      },
      move(event) {
        // Optional: Add extra visual feedback during dragging if necessary
      },
      async end(event) {
        draggingEl.style.opacity = '1';

        // Reset the cursor to grab when dragging ends
        document.body.style.cursor = 'grab'; 

        const dropPos = getCanvasDropPosition(event.clientX, event.clientY);

        // If the drop position is valid, spawn the models
        if (dropPos && draggingId === 'case' && !caseModel) {
          const modelUrl = selectedCaseModelUrl;

          if (modelUrl) {
            await spawnCase(dropPos, selectedCaseModelUrl);
            wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
          } else {
            console.warn('No model Url provided for case');
          }
        } else if (dropPos && draggingId === 'motherboard' && caseModel) {
          spawnMoboAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'psu' && caseModel) {
          spawnPsuAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'psu' && caseModel) {
          spawnPsuAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'cpu' && moboModel) {
          spawnCpuAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'cooler' && moboModel) {
          spawnCoolerAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'ssd' && moboModel) {
          spawnSsdAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'gpu' && moboModel) {
          spawnGpuAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } else if (dropPos && draggingId === 'ram' && moboModel) {
          spawnRamAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        } 

        // If drop was unsuccessful, remove the dragged model (if any)
        if (!wasDroppedSuccessfully) {
          if (draggingId === 'case' && caseModel) {
            scene.remove(caseModel);  // Remove the case if it was dropped unsuccessfully
            caseModel = null;
          }
          if (draggingId === 'motherboard' && moboModel) {
            scene.remove(moboModel);  // Remove the GPU if it was dropped unsuccessfully
            moboModel = null;
          }
          if (draggingId === 'psu' && psuModel) {
            scene.remove(psuModel);  // Remove the GPU if it was dropped unsuccessfully
            psuModel = null;
          }
          if (draggingId === 'psu' && psuModel) {
            scene.remove(psuModel);  // Remove the GPU if it was dropped unsuccessfully
            psuModel = null;
          }
          if (draggingId === 'cpu' && cpuModel) {
            scene.remove(cpuModel);  // Remove the GPU if it was dropped unsuccessfully
            cpuModel = null;
          }
          if (draggingId === 'cooler' && coolerModel) {
            scene.remove(coolerModel);  // Remove the GPU if it was dropped unsuccessfully
            coolerModel = null;
          }
          if (draggingId === 'ssd' && ssdModel) {
            scene.remove(ssdModel);  // Remove the GPU if it was dropped unsuccessfully
            ssdModel = null;
          }
          if (draggingId === 'gpu' && gpuModel) {
            scene.remove(gpuModel);  // Remove the GPU if it was dropped unsuccessfully
            gpuModel = null;
          }
          if (draggingId === 'ram' && ramModels) {
            scene.remove(ramModels);  // Remove the GPU if it was dropped unsuccessfully
            ramModels = null;
          }

          // Reset the marker to the center when the drop fails
          if (casemarker) {
            casemarker.position.set(0, 0, 0); // Reset position to center
          }
        }

        // Revert the slot highlight after dragging ends
        if (casemarker) {
          scene.remove(casemarker);
          casemarker = null;
        }
        if (mobomarker) {
          scene.remove(mobomarker);
          mobomarker = null;
        }
        if (psumarker) {
          scene.remove(psumarker);
          psumarker = null;
        }
        if (psumarker) {
          scene.remove(psumarker);
          psumarker = null;
        }
        if (cpumarker) {
          scene.remove(cpumarker);
          cpumarker = null;
        }
        if (coolermarker) {
          scene.remove(coolermarker);
          coolermarker = null;
        }
        if (ssdmarker) {
          scene.remove(ssdmarker);
          ssdmarker = null;
        }
        if (gpumarker) {
          scene.remove(gpumarker);
          gpumarker = null;
        }
        if (rammarker) {
          scene.remove(rammarker);
          rammarker = null;
        }

        if (originalSlotMaterial) {
          const moboSlot = caseModel.getObjectByName('Slot_Mobo');
          if (moboSlot) {
            moboSlot.material = originalSlotMaterial; // Restore the original material
          }
        }

        // Reset dragging state
        draggingId = null;
        draggingEl = null;
        originalSlotMaterial = null;
        wasDroppedSuccessfully = false;
      }
    }
  });
}

function getCanvasDropPosition(clientX, clientY) {
  const rect = renderer.domElement.getBoundingClientRect();

  if (
    clientX < rect.left || clientX > rect.right ||
    clientY < rect.top || clientY > rect.bottom
  ) {
    return null;
  }

  const x = ((clientX - rect.left) / rect.width) * 2 - 1;
  const y = - ((clientY - rect.top) / rect.height) * 2 + 1;

  const mouseVector = new THREE.Vector2(x, y);
  const raycaster = new THREE.Raycaster();

  raycaster.setFromCamera(mouseVector, camera);

  const planeZ = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
  const intersectionPoint = new THREE.Vector3();

  raycaster.ray.intersectPlane(planeZ, intersectionPoint);

  return intersectionPoint;
}

async function spawnCase(position, modelUrl) {
  if (caseModel) return; // only one case at a time

  try {
    const model = await loadGLTFModel(modelUrl);
    model.position.copy(position);
    model.scale.setScalar(1); // Shrinks uniformly
    scene.add(model);
    caseModel = model;

    // CONTROL THE ROTATION || FOCUS THE ROTATION ON THE MODEL
    controls.target.copy(model.position);
    controls.update();

    // MOBO SLOT
    const moboSlot = model.getObjectByName('Slot_Mobo');
    if (moboSlot) {
      moboSlotPosition = new THREE.Vector3();
      moboSlot.getWorldPosition(moboSlotPosition);
      console.log('GPU slot position:', moboSlotPosition);
    } else {
      moboSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('GPU slot not found in case model');
    }

    // PSU SLOT
    const psuSlot = model.getObjectByName('Slot_Psu');
    if (psuSlot) {
      psuSlotPosition = new THREE.Vector3();
      psuSlot.getWorldPosition(psuSlotPosition);
      console.log('PSU slot position:', psuSlotPosition);
    } else {
      psuSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('PSU slot not found in case model');
    }


  } catch (error) {
    console.error('Failed to load case model', error);
  }
}

async function spawnPsuAtSlot() {
  if (!psuSlotPosition) {
    alert('PSU slot position unknown');
    return;
  }

  if (!selectedPsuModelUrl) {
    alert('Please select a PSU model first.');
    return;
  }

  if (psuModel) {
    scene.remove(psuModel);
    psuModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedPsuModelUrl);
    model.position.copy(psuSlotPosition);
    
    // APPLY ROTATION FROM SLOT
    const psuSlot = caseModel.getObjectByName('Slot_Psu');
    if (psuSlot) {
      model.rotation.copy(psuSlot.rotation);
    }
    
    scene.add(model);
    psuModel = model;
  } catch (error) {
    console.error('Failed to load PSU model', error);
  }
}

async function spawnMoboAtSlot() {
  if (!moboSlotPosition) {
    alert('mobo slot position unknown');
    return;
  }

  if (!selectedMoboModelUrl) {
    alert('Please select a mobo model first.');
    return;
  }

  if (moboModel) {
    scene.remove(moboModel);
    moboModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedMoboModelUrl);
    model.position.copy(moboSlotPosition);
    
    // APPLY ROTATION FROM SLOT
    const moboSlot = caseModel.getObjectByName('Slot_Mobo');
    if (moboSlot) {
      model.rotation.copy(moboSlot.rotation);
    }
    
    scene.add(model);
    moboModel = model;

    // CPU SLOT
    // CPU SLOT
    const cpuSlot = model.getObjectByName('Slot_Cpu');
    if (cpuSlot) {
      cpuSlotPosition = new THREE.Vector3();
      cpuSlot.getWorldPosition(cpuSlotPosition);
      console.log('CPU slot position:', cpuSlotPosition);
    } else {
      cpuSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('CPU slot not found in mobo model');
    }

    // Cooler SLOT
    const coolerSlot = model.getObjectByName('Slot_Cooler');
    if (coolerSlot) {
      coolerSlotPosition = new THREE.Vector3();
      coolerSlot.getWorldPosition(coolerSlotPosition);
      console.log('Cooler slot position:', coolerSlotPosition);
    } else {
      coolerSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('Cooler slot not found in mobo model');
    }

    // SSD SLOT
    const ssdSlot = model.getObjectByName('Slot_Ssd');
    if (ssdSlot) {
      ssdSlotPosition = new THREE.Vector3();
      ssdSlot.getWorldPosition(ssdSlotPosition);
      console.log('SSD slot position:', ssdSlotPosition);
    } else {
      ssdSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('SSD slot not found in mobo model');
    }

    // GPU SLOT
    const gpuSlot = model.getObjectByName('Slot_Gpu');
    if (gpuSlot) {
      gpuSlotPosition = new THREE.Vector3();
      gpuSlot.getWorldPosition(gpuSlotPosition);
      console.log('GPU slot position:', gpuSlotPosition);
    } else {
      gpuSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('GPU slot not found in mobo model');
    }

    // RAM SLOT
    const ramSlot01 = model.getObjectByName('Slot_Ram1');
    const ramSlot02 = model.getObjectByName('Slot_Ram2'); 

    if (ramSlot01 || ramSlot02) {
      console.log('RAM slots found in mobo model');
      // We don't need to store positions here since we'll use the slot objects directly
    } else {
      console.warn('RAM slots not found in mobo model');
    }
  } catch (error) {
    console.error('Failed to load Ram model', error);
  }
}

async function spawnCpuAtSlot() {
  if (!cpuSlotPosition) {
    alert('CPU slot position unknown');
    return;
  }

  if (!selectedCpuModelUrl) {
    alert('Please select a CPU model first.');
    return;
  }

  if (cpuModel) {
    scene.remove(cpuModel);
    cpuModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedCpuModelUrl);
    model.position.copy(cpuSlotPosition);
    scene.add(model);
    cpuModel = model;
  } catch (error) {
    console.error('Failed to load CPU model', error);
  }
}

async function spawnCoolerAtSlot() {
  if (!coolerSlotPosition) {
    alert('Cooler slot position unknown');
    return;
  }

  if (!selectedCoolerModelUrl) {
    alert('Please select a Cooler model first.');
    return;
  }

  if (coolerModel) {
    scene.remove(coolerModel);
    coolerModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedCoolerModelUrl);
    model.position.copy(coolerSlotPosition);
    scene.add(model);
    coolerModel = model;
  } catch (error) {
    console.error('Failed to load Cooler model', error);
  }
}

async function spawnSsdAtSlot() {
  if (!ssdSlotPosition) {
    alert('Ssd slot position unknown');
    return;
  }

  if (!selectedSsdModelUrl) {
    alert('Please select a Ssd model first.');
    return;
  }

  if (ssdModel) {
    scene.remove(ssdModel);
    ssdModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedSsdModelUrl);
    model.position.copy(ssdSlotPosition);
    
    // APPLY ROTATION FROM SLOT
    const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
    if (ssdSlot) {
      model.rotation.copy(ssdSlot.rotation);
    }
    
    scene.add(model);
    ssdModel = model;
  } catch (error) {
    console.error('Failed to load Ssd model', error);
  }
}

async function spawnGpuAtSlot() {
  if (!gpuSlotPosition) {
    alert('Gpu slot position unknown');
    return;
  }

  if (!selectedGpuModelUrl) {
    alert('Please select a Gpu model first.');
    return;
  }

  if (gpuModel) {
    scene.remove(gpuModel);
    gpuModel = null;
  }
  
  try {
    const model = await loadGLTFModel(selectedGpuModelUrl);
    model.position.copy(gpuSlotPosition);
    
    scene.add(model);
    gpuModel = model;
  } catch (error) {
    console.error('Failed to load Gpu model', error);
  }
}

async function spawnRamAtSlot() {
  if (!selectedRamModelUrl) {
    alert('Please select a Ram model first.');
    return;
  }

  // Remove existing RAM models if any
  if (ramModels.length > 0) {
    ramModels.forEach(ram => scene.remove(ram));
    ramModels = [];
  }
  
  try {
    // Load the base RAM model
    const baseRamModel = await loadGLTFModel(selectedRamModelUrl);
    
    // Get all RAM slots from the motherboard
    const slotRam01 = moboModel.getObjectByName('Slot_Ram1');
    const slotRam02 = moboModel.getObjectByName('Slot_Ram2');
    
    // Clone and position RAM sticks in available slots
    if (slotRam01) {
      const ram1 = baseRamModel.clone();
      
      // Get WORLD position (like GPU does)
      const worldPosition = new THREE.Vector3();
      slotRam01.getWorldPosition(worldPosition);
      
      ram1.position.copy(worldPosition);
      ram1.rotation.copy(slotRam01.rotation);
      scene.add(ram1);
      ramModels.push(ram1);
    }
    
    if (slotRam02) {
      const ram2 = baseRamModel.clone();
      
      // Get WORLD position (like GPU does)
      const worldPosition = new THREE.Vector3();
      slotRam02.getWorldPosition(worldPosition);
      
      ram2.position.copy(worldPosition);
      ram2.rotation.copy(slotRam02.rotation);
      scene.add(ram2);
      ramModels.push(ram2);
    }
    
    if (ramModels.length === 0) {
      alert('No RAM slots found on the motherboard');
      return;
    }
    
    console.log(`Spawned ${ramModels.length} RAM sticks`);
    
  } catch (error) {
    console.error('Failed to load Ram model', error);
  }
}

function reloadScene() {
    // Remove case model
    if (caseModel) {
        scene.remove(caseModel);
        caseModel = null;
    }

    // Remove motherboard model
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }

    // Remove psu model
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }

    // Remove psu model
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }

    // Remove CPU model
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }

    // Remove cooler model
    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }

    // Remove ssd model
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }

    // Remove gpu model
    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }

    // Remove ALL ram models
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }


    // Reset the camera controls target to the origin (or wherever you prefer)
    controls.target.set(0, 0, 0);
    controls.update();

    // Log to confirm scene is reset
    console.log('Scene has been reloaded.');
}


document.getElementById('reloadButton').addEventListener('click', function() {
    reloadScene();
});