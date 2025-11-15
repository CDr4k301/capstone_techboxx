// 3D JS
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
let ramModels = [];
let moboSlotPosition = null;
let cpuSlotPosition = null;
let psuSlotPosition = null;
let coolerSlotPosition = null;
let ssdSlotPosition = null;
let gpuSlotPosition = null;
let selectedCaseModelUrl = null;
let selectedMoboModelUrl = null;
let selectedCpuModelUrl = null;
let selectedPsuModelUrl = null;
let selectedCoolerModelUrl = null;
let selectedSsdModelUrl = null;
let selectedGpuModelUrl = null;
let selectedRamModelUrl = null;

let caseMarker = null;
let moboMarker = null;
let draggingId = null;
let draggingEl = null;

init();
setupCatalogClickHandlers();
animate();

function init() {
    // Scene
    scene = new THREE.Scene();
    scene.background = null; // transparent background

    // Camera
    const container = document.getElementById('canvas-container');
    const width = container.clientWidth;
    const height = container.clientHeight;

    camera = new THREE.PerspectiveCamera(30, width / height, 0.1, 1000);
    camera.position.set(20, 0, 0);

    // Renderer
    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    container.appendChild(renderer.domElement);

    // Controls
    controls = new OrbitControls(camera, renderer.domElement);

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    const dirLight = new THREE.DirectionalLight(0xffffff, 7.0);
    dirLight.position.set(2, 10, 5);
    scene.add(dirLight);

    // Resize listener
    window.addEventListener('resize', () => {
        const width = container.clientWidth;
        const height = container.clientHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    });
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

// Catalog click (optional: preselect URLs)
function setupCatalogClickHandlers() {
  document.querySelectorAll('.build-catalog, .component-button').forEach(item => {
    item.addEventListener('click', () => {
      const modelUrl = item.getAttribute('data-model');
      const type = item.getAttribute('data-type');

      if (!modelUrl) {
        console.log('Model not available for this component.');
        return;
      }
      if (type === 'case') {
        selectedCaseModelUrl = modelUrl;
        console.log('Selected case URL:', selectedCaseModelUrl);
      } else if (type === 'motherboard') {
        selectedMoboModelUrl = modelUrl;
        console.log('Selected motherboard URL:', selectedMoboModelUrl);
      } else if (type === 'cpu') {
        selectedCpuModelUrl = modelUrl;
        console.log('Selected CPU URL:', selectedCpuModelUrl);
      } else if (type === 'psu') {
        selectedPsuModelUrl = modelUrl;
        console.log('Selected PSU URL:', selectedPsuModelUrl);
      } else if (type === 'cooler') {
        selectedCoolerModelUrl = modelUrl;
        console.log('Selected cooler URL:', selectedCoolerModelUrl);
      } else if (type === 'ssd') {
        selectedSsdModelUrl = modelUrl;
        console.log('Selected SSD URL:', selectedSsdModelUrl);
      } else if (type === 'gpu') {
        selectedGpuModelUrl = modelUrl;
        console.log('Selected GPU URL:', selectedGpuModelUrl);
      } else if (type === 'ram') {
        selectedRamModelUrl = modelUrl;
        console.log('Selected RAM URL:', selectedRamModelUrl);
      }
    });
  });
}

// Interact.js drag/drop
interact('.component-button').draggable({
    listeners: {
        start(event) {
            draggingId = event.target.getAttribute('data-type') || event.target.id;
            draggingEl = event.target;
            draggingEl.style.opacity = '0.5';
            document.body.style.cursor = 'grabbing';

            if (draggingId === 'case' && !caseModel) {
                caseMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(4, 4, 4),
                    new THREE.MeshStandardMaterial({
                    color: 0x0000ff,
                    emissive: 0x000066,
                    transparent: true,
                    opacity: 0.3,
                    })
                );
                
                caseMarker.position.set(0, 0, 0);
                scene.add(caseMarker);
            }

            if (draggingId === 'motherboard' && caseModel) {
                const moboSlot = caseModel.getObjectByName('Slot_Mobo');
                if (moboSlot) {
                    moboMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(3, 3, 0.1),
                    new THREE.MeshStandardMaterial({
                        color: 0x00ff00,
                        emissive: 0x003300,
                        transparent: true,
                        opacity: 0.4,
                    })
                    );

                    moboMarker.rotation.x = 0;
                    moboMarker.rotation.y = Math.PI / 2;
                    moboMarker.rotation.z = 0;
                    mobomarker.position.set(moboSlotPosition.x, moboSlotPosition.y - 0.9, moboSlotPosition.z - 0.61); // Position the mobomarker
                    scene.add(moboMarker);
                }
            }

            // Add markers for other components
            if (draggingId === 'cpu' && moboModel) {
                const cpuSlot = moboModel.getObjectByName('Slot_Cpu');
                if (cpuSlot) {
                    const cpumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    cpumarker.rotation.x = 0;
                    cpumarker.rotation.y = Math.PI / 2;
                    cpumarker.rotation.z = 0;
                    
                    const cpuSlotPosition = new THREE.Vector3();
                    cpuSlot.getWorldPosition(cpuSlotPosition);
                    cpumarker.position.set(cpuSlotPosition.x, cpuSlotPosition.y + -1, cpuSlotPosition.z + -1.4);
                    scene.add(cpumarker);
                }
            }

            if (draggingId === 'psu' && caseModel) {
                const psuSlot = caseModel.getObjectByName('Slot_Psu');
                if (psuSlot) {
                    const psumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(1, 0.8, 2),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    psumarker.rotation.x = 0;
                    psumarker.rotation.y = Math.PI / 2;
                    psumarker.rotation.z = 0;
                    
                    const psuSlotPosition = new THREE.Vector3();
                    psuSlot.getWorldPosition(psuSlotPosition);
                    psumarker.position.set(psuSlotPosition.x + 1.4, psuSlotPosition.y + 0.4, psuSlotPosition.z + -1);
                    scene.add(psumarker);
                }
            }

            if (draggingId === 'cooler' && moboModel) {
                const coolerSlot = moboModel.getObjectByName('Slot_Cooler');
                if (coolerSlot) {
                    const coolermarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    coolermarker.rotation.x = 0;
                    coolermarker.rotation.y = Math.PI / 2;
                    coolermarker.rotation.z = 0;
                    
                    const coolerSlotPosition = new THREE.Vector3();
                    coolerSlot.getWorldPosition(coolerSlotPosition);
                    coolermarker.position.set(coolerSlotPosition.x, coolerSlotPosition.y + -1, coolerSlotPosition.z + -1.4);
                    scene.add(coolermarker);
                }
            }

            if (draggingId === 'ssd' && moboModel) {
                const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
                if (ssdSlot) {
                    const ssdmarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    ssdmarker.rotation.x = 0;
                    ssdmarker.rotation.y = Math.PI / 2;
                    ssdmarker.rotation.z = 0;
                    
                    const ssdSlotPosition = new THREE.Vector3();
                    ssdSlot.getWorldPosition(ssdSlotPosition);
                    ssdmarker.position.set(ssdSlotPosition.x, ssdSlotPosition.y + -1, ssdSlotPosition.z + -1.4);
                    scene.add(ssdmarker);
                }
            }

            if (draggingId === 'gpu' && moboModel) {
                const gpuSlot = moboModel.getObjectByName('Slot_Gpu');
                if (gpuSlot) {
                    const gpumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    gpumarker.rotation.x = 0;
                    gpumarker.rotation.y = Math.PI / 2;
                    gpumarker.rotation.z = 0;
                    
                    const gpuSlotPosition = new THREE.Vector3();
                    gpuSlot.getWorldPosition(gpuSlotPosition);
                    gpumarker.position.set(gpuSlotPosition.x, gpuSlotPosition.y + -1, gpuSlotPosition.z + -1.4);
                    scene.add(gpumarker);
                }
            }

            if (draggingId === 'ram' && moboModel) {
                const ramSlot01 = moboModel.getObjectByName('Slot_Ram1');
                const ramSlot02 = moboModel.getObjectByName('Slot_Ram2');
                
                if (ramSlot01 || ramSlot02) {
                    const rammarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    rammarker.rotation.x = 0;
                    rammarker.rotation.y = Math.PI / 2;
                    rammarker.rotation.z = 0;
                    
                    const firstRamSlot = ramSlot01 || ramSlot02;
                    const ramSlotPosition = new THREE.Vector3();
                    firstRamSlot.getWorldPosition(ramSlotPosition);
                    rammarker.position.set(ramSlotPosition.x, ramSlotPosition.y + -1, ramSlotPosition.z + -1.4);
                    scene.add(rammarker);
                }
            }
        },
        move(event) {
        // Optional: visual feedback while dragging
        },
        async end(event) {
            draggingEl.style.opacity = '1';
            document.body.style.cursor = 'grab';

            const dropPos = getCanvasDropPosition(event.clientX, event.clientY);

            if (dropPos && draggingId === 'case' && !caseModel) {
                if (selectedCaseModelUrl) {
                await spawnCase(dropPos, selectedCaseModelUrl);
                }
            }
            if (dropPos && draggingId === 'motherboard' && caseModel) {
                await spawnMoboAtSlot();
            }
            if (dropPos && draggingId === 'cpu' && moboModel) {
                await spawnCpuAtSlot();
            }
            if (dropPos && draggingId === 'psu' && caseModel) {
                await spawnPsuAtSlot();
            }
            if (dropPos && draggingId === 'cooler' && moboModel) {
                await spawnCoolerAtSlot();
            }
            if (dropPos && draggingId === 'ssd' && moboModel) {
                await spawnSsdAtSlot();
            }
            if (dropPos && draggingId === 'gpu' && moboModel) {
                await spawnGpuAtSlot();
            }
            if (dropPos && draggingId === 'ram' && moboModel) {
                await spawnRamAtSlot();
            }

            // Clean up markers
            if (caseMarker) {
                scene.remove(caseMarker);
                caseMarker = null;
            }
            if (moboMarker) {
                scene.remove(moboMarker);
                moboMarker = null;
            }

            // Clean up other markers
            scene.traverse((object) => {
                if (object.isMesh && object.material && object.material.emissive && 
                    object.material.emissive.equals(new THREE.Color(0x003300))) {
                    scene.remove(object);
                }
            });

            draggingId = null;
            draggingEl = null;
        }
    }
});

function getCanvasDropPosition(clientX, clientY) {
    const rect = renderer.domElement.getBoundingClientRect();
    if (
        clientX < rect.left || clientX > rect.right ||
        clientY < rect.top || clientY > rect.bottom
    ) {
        return null;
    }
    const x = ((clientX - rect.left) / rect.width) * 2 - 1;
    const y = -((clientY - rect.top) / rect.height) * 2 + 1;

    const mouseVector = new THREE.Vector2(x, y);
    const raycaster = new THREE.Raycaster();
    raycaster.setFromCamera(mouseVector, camera);

    const planeZ = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
    const intersectionPoint = new THREE.Vector3();
    raycaster.ray.intersectPlane(planeZ, intersectionPoint);
    return intersectionPoint;
}

async function spawnCase(position, modelUrl) {
    if (caseModel) return;
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(position);
        model.scale.setScalar(1);
        scene.add(model);
        caseModel = model;

        // Focus controls on the case
        controls.target.copy(model.position);
        controls.update();

        // Get all slot positions
        const moboSlot = model.getObjectByName('Slot_Mobo');
        if (moboSlot) {
            moboSlotPosition = new THREE.Vector3();
            moboSlot.getWorldPosition(moboSlotPosition);
            console.log('Motherboard slot position:', moboSlotPosition);
        } else {
            moboSlotPosition = new THREE.Vector3(0, 0, 0);
            console.warn('Motherboard slot not found in case model');
        }

        const psuSlot = model.getObjectByName('Slot_Psu');
        if (psuSlot) {
            psuSlotPosition = new THREE.Vector3();
            psuSlot.getWorldPosition(psuSlotPosition);
            console.log('PSU slot position:', psuSlotPosition);
        } else {
            psuSlotPosition = new THREE.Vector3(0, 0, 0);
            console.warn('PSU slot not found in case model');
        }

    } catch (err) {
        console.error('Failed to load case model', err);
    }
}

async function spawnMoboAtSlot() {
    if (!moboSlotPosition) {
        alert('Motherboard slot position unknown');
        return;
    }
    if (!selectedMoboModelUrl) {
        alert('Please select a motherboard model first.');
        return;
    }
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedMoboModelUrl);
        model.position.copy(moboSlotPosition);
        
        // Apply rotation from slot
        const moboSlot = caseModel.getObjectByName('Slot_Mobo');
        if (moboSlot) {
            model.rotation.copy(moboSlot.rotation);
        }
        
        scene.add(model);
        moboModel = model;

        // Get motherboard component slots
        const cpuSlot = moboModel.getObjectByName('Slot_Cpu');
        if (cpuSlot) {
            cpuSlotPosition = new THREE.Vector3();
            cpuSlot.getWorldPosition(cpuSlotPosition);
            console.log('CPU slot position:', cpuSlotPosition);
        }

        const coolerSlot = moboModel.getObjectByName('Slot_Cooler');
        if (coolerSlot) {
            coolerSlotPosition = new THREE.Vector3();
            coolerSlot.getWorldPosition(coolerSlotPosition);
            console.log('Cooler slot position:', coolerSlotPosition);
        }

        const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
        if (ssdSlot) {
            ssdSlotPosition = new THREE.Vector3();
            ssdSlot.getWorldPosition(ssdSlotPosition);
            console.log('SSD slot position:', ssdSlotPosition);
        }

        const gpuSlot = moboModel.getObjectByName('Slot_Gpu');
        if (gpuSlot) {
            gpuSlotPosition = new THREE.Vector3();
            gpuSlot.getWorldPosition(gpuSlotPosition);
            console.log('GPU slot position:', gpuSlotPosition);
        }

    } catch (err) {
        console.error('Failed to load motherboard model', err);
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
    } catch (err) {
        console.error('Failed to load CPU model', err);
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
        
        // Apply rotation from slot
        const psuSlot = caseModel.getObjectByName('Slot_Psu');
        if (psuSlot) {
            model.rotation.copy(psuSlot.rotation);
        }
        
        scene.add(model);
        psuModel = model;
    } catch (err) {
        console.error('Failed to load PSU model', err);
    }
}

async function spawnCoolerAtSlot() {
    if (!coolerSlotPosition) {
        alert('Cooler slot position unknown');
        return;
    }
    if (!selectedCoolerModelUrl) {
        alert('Please select a cooler model first.');
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
    } catch (err) {
        console.error('Failed to load cooler model', err);
    }
}

async function spawnSsdAtSlot() {
    if (!ssdSlotPosition) {
        alert('SSD slot position unknown');
        return;
    }
    if (!selectedSsdModelUrl) {
        alert('Please select an SSD model first.');
        return;
    }
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedSsdModelUrl);
        model.position.copy(ssdSlotPosition);
        
        // Apply rotation from slot
        const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
        if (ssdSlot) {
            model.rotation.copy(ssdSlot.rotation);
        }
        
        scene.add(model);
        ssdModel = model;
    } catch (err) {
        console.error('Failed to load SSD model', err);
    }
}

async function spawnGpuAtSlot() {
    if (!gpuSlotPosition) {
        alert('GPU slot position unknown');
        return;
    }
    if (!selectedGpuModelUrl) {
        alert('Please select a GPU model first.');
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
    } catch (err) {
        console.error('Failed to load GPU model', err);
    }
}

async function spawnRamAtSlot() {
    if (!selectedRamModelUrl) {
        alert('Please select a RAM model first.');
        return;
    }

    // Remove existing RAM models if any
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }
    
    try {
        const baseRamModel = await loadGLTFModel(selectedRamModelUrl);
        
        // Get all RAM slots from the motherboard
        const slotRam01 = moboModel.getObjectByName('Slot_Ram1');
        const slotRam02 = moboModel.getObjectByName('Slot_Ram2');
        
        // Clone and position RAM sticks in available slots
        if (slotRam01) {
            const ram1 = baseRamModel.clone();
            const worldPosition = new THREE.Vector3();
            slotRam01.getWorldPosition(worldPosition);
            ram1.position.copy(worldPosition);
            ram1.rotation.copy(slotRam01.rotation);
            scene.add(ram1);
            ramModels.push(ram1);
        }
        
        if (slotRam02) {
            const ram2 = baseRamModel.clone();
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
        
    } catch (err) {
        console.error('Failed to load RAM model', err);
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

    // Remove CPU model
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }

    // Remove PSU model
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }

    // Remove cooler model
    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }

    // Remove SSD model
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }

    // Remove GPU model
    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }

    // Remove ALL ram models
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }

    // Reset the camera controls target to the origin
    controls.target.set(0, 0, 0);
    controls.update();

    // Log to confirm scene is reset
    console.log('Scene has been reloaded.');
}

// Add reload button event listener if it exists
if (document.getElementById('reloadButton')) {
    document.getElementById('reloadButton').addEventListener('click', function() {
        reloadScene();
    });
}