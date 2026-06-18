import * as THREE from 'three';
import { FBXLoader } from 'three/addons/loaders/FBXLoader.js';

(function () {
    const canvas = document.getElementById('equipment3dCanvas');
    const title = document.getElementById('equipmentViewerTitle');
    const description = document.getElementById('equipmentViewerDescription');
    const hint = document.getElementById('equipmentViewerHint');
    const buttons = Array.from(document.querySelectorAll('[data-viewer-device]'));

    if (!canvas) return;
    const devices = {
        hub: {
            title: 'Hub 2 (4G) Jeweller',
            description: 'Panel central AJAX cargado desde el modelo 3D real de la carpeta de assets.',
            modelUrl: 'img/Paquetes AJAX/Modelos 3d/Hub 2 (4G) Jeweller.fbx',
            build: buildHub
        },
        doorprotect: {
            title: 'DoorProtect Jeweller',
            description: 'Detector inalámbrico de apertura para puertas y ventanas, cargado desde el modelo 3D real.',
            modelUrl: 'img/Paquetes AJAX/Modelos 3d/Render - Ajax DoorProtect U Jeweller - Black - front/model.fbx',
            build: buildSensor
        },
        motioncam: {
            title: 'MotionCam (PhOD) Jeweller',
            description: 'Detector de movimiento con verificación fotográfica, usando el FBX y textura agregados al proyecto.',
            modelUrl: 'img/Paquetes AJAX/Modelos 3d/Render - Ajax MotionCam U (PhOD) Jeweller - Black - front/model.fbx',
            build: buildCamera
        },
        keypad: {
            title: 'KeyPad Jeweller',
            description: 'Teclado táctil para armar, desarmar y controlar el sistema con rapidez.',
            modelUrl: 'img/Paquetes AJAX/Modelos 3d/Render - Ajax KeyPad Plus Jeweller-Black-front/model.fbx',
            build: buildKeypad
        }
    };

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, preserveDrawingBuffer: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 1.15, 7);
    camera.lookAt(0, 0, 0);

    const keyLight = new THREE.DirectionalLight(0xffffff, 2.8);
    keyLight.position.set(3.5, 4.5, 5);
    scene.add(keyLight);
    scene.add(new THREE.HemisphereLight(0xbddcff, 0x07142d, 2.1));

    const fillLight = new THREE.PointLight(0xf6eb17, 22, 12);
    fillLight.position.set(-3, 1.5, 3.5);
    scene.add(fillLight);

    const root = new THREE.Group();
    scene.add(root);
    const modelCache = {};

    let activeDevice = 'hub';
    let targetRotationY = 0;
    let targetRotationX = -0.05;
    let isDragging = false;
    let lastPointer = { x: 0, y: 0 };
    let isVisible = true;

    const darkMaterial = new THREE.MeshStandardMaterial({
        color: 0x101722,
        roughness: 0.42,
        metalness: 0.22
    });
    const faceMaterial = new THREE.MeshStandardMaterial({
        color: 0xf8fafc,
        roughness: 0.34,
        metalness: 0.05
    });
    const accentMaterial = new THREE.MeshStandardMaterial({
        color: 0xf6eb17,
        roughness: 0.26,
        metalness: 0.14,
        emissive: 0x6b6200,
        emissiveIntensity: 0.16
    });
    const glassMaterial = new THREE.MeshPhysicalMaterial({
        color: 0x0a1528,
        roughness: 0.08,
        metalness: 0.08,
        transmission: 0.08,
        clearcoat: 1,
        clearcoatRoughness: 0.12
    });

    function roundedBox(width, height, depth, material) {
        const geometry = new THREE.BoxGeometry(width, height, depth, 6, 6, 6);
        return new THREE.Mesh(geometry, material);
    }

    function add(mesh, position, rotation, scale) {
        if (position) mesh.position.set(position[0], position[1], position[2]);
        if (rotation) mesh.rotation.set(rotation[0], rotation[1], rotation[2]);
        if (scale) mesh.scale.set(scale[0], scale[1], scale[2]);
        root.add(mesh);
        return mesh;
    }

    function clearRoot() {
        while (root.children.length) {
            const child = root.children.pop();
            disposeObject(child);
        }
    }

    function disposeObject(object) {
        object.traverse?.((child) => {
            if (child.geometry && !child.userData.preserveGeometry) child.geometry.dispose();
            if (child.material && !child.userData.preserveMaterial) {
                const materials = Array.isArray(child.material) ? child.material : [child.material];
                materials.forEach((material) => {
                    Object.values(material).forEach((value) => {
                        if (value && value.isTexture) value.dispose();
                    });
                    material.dispose();
                });
            }
        });
    }

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    function prepareModel(model) {
        model.traverse((child) => {
            if (!child.isMesh) return;
            child.castShadow = false;
            child.receiveShadow = false;
            child.userData.preserveGeometry = true;
            child.userData.preserveMaterial = true;
            if (child.material) {
                const materials = Array.isArray(child.material) ? child.material : [child.material];
                materials.forEach((material) => {
                    material.side = THREE.DoubleSide;
                    if (material.map) material.map.colorSpace = THREE.SRGBColorSpace;
                    material.needsUpdate = true;
                });
            }
        });

        const box = new THREE.Box3().setFromObject(model);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const maxDimension = Math.max(size.x, size.y, size.z) || 1;
        const scale = 2.35 / maxDimension;

        model.position.sub(center);
        model.scale.multiplyScalar(scale);
        model.rotation.set(0, 0, 0);
        return model;
    }

    function addModelToScene(deviceKey, model) {
        if (activeDevice !== deviceKey) return;
        clearRoot();
        const clone = model.clone(true);
        root.add(clone);
        root.rotation.set(-0.05, 0, 0);
        targetRotationX = -0.05;
        targetRotationY = 0;
        setHint('Arrastra para girar');
    }

    function loadModel(deviceKey, device) {
        if (!device.modelUrl) {
            device.build();
            setHint('Arrastra para girar');
            return;
        }

        if (modelCache[deviceKey]) {
            addModelToScene(deviceKey, modelCache[deviceKey]);
            return;
        }

        device.build();
        setHint('Cargando modelo 3D');
        const modelFolder = device.modelUrl.slice(0, device.modelUrl.lastIndexOf('/') + 1);
        const loader = new FBXLoader();
        loader.setResourcePath(encodeURI(modelFolder));
        loader.load(
            encodeURI(device.modelUrl),
            (model) => {
                modelCache[deviceKey] = prepareModel(model);
                addModelToScene(deviceKey, modelCache[deviceKey]);
            },
            (event) => {
                if (!event.total) return;
                const progress = Math.min(99, Math.round((event.loaded / event.total) * 100));
                setHint(`Cargando ${progress}%`);
            },
            () => {
                clearRoot();
                device.build();
                setHint('Modelo no disponible');
            }
        );
    }

    function buildHub() {
        add(roundedBox(2.15, 2.15, 0.34, darkMaterial), [0, 0, 0]);
        add(roundedBox(1.82, 1.82, 0.39, faceMaterial), [0, 0, 0.08]);
        add(new THREE.Mesh(new THREE.CircleGeometry(0.38, 48), accentMaterial), [0, 0.08, 0.3]);
        add(roundedBox(0.9, 0.12, 0.08, darkMaterial), [0, -0.58, 0.34]);
    }

    function buildSensor() {
        add(roundedBox(1.12, 2.35, 0.48, faceMaterial), [0, 0, 0]);
        add(roundedBox(0.82, 1.12, 0.14, glassMaterial), [0, 0.22, 0.32]);
        add(new THREE.Mesh(new THREE.SphereGeometry(0.35, 40, 24), glassMaterial), [0, 0.26, 0.42], null, [1, 0.72, 0.32]);
        add(roundedBox(0.62, 0.1, 0.08, accentMaterial), [0, -0.84, 0.34]);
    }

    function buildCamera() {
        add(new THREE.Mesh(new THREE.CylinderGeometry(0.95, 0.95, 0.42, 48), darkMaterial), [0, 0.75, 0], [Math.PI / 2, 0, 0]);
        add(new THREE.Mesh(new THREE.CylinderGeometry(0.78, 0.92, 1.25, 48), faceMaterial), [0, 0, 0], [Math.PI / 2, 0, 0]);
        add(new THREE.Mesh(new THREE.CylinderGeometry(0.52, 0.52, 0.22, 48), glassMaterial), [0, -0.02, 0.68], [Math.PI / 2, 0, 0]);
        add(new THREE.Mesh(new THREE.SphereGeometry(0.28, 36, 18), glassMaterial), [0, -0.02, 0.83]);
        add(roundedBox(1.2, 0.12, 0.08, accentMaterial), [0, -0.74, 0.12]);
    }

    function buildSiren() {
        add(roundedBox(2.15, 1.55, 0.5, faceMaterial), [0, 0, 0]);
        for (let index = -2; index <= 2; index += 1) {
            add(roundedBox(1.35, 0.08, 0.08, darkMaterial), [0, index * 0.19, 0.33]);
        }
        add(new THREE.Mesh(new THREE.CircleGeometry(0.22, 32), accentMaterial), [0, 0.54, 0.35]);
    }

    function buildKeypad() {
        add(roundedBox(1.38, 2.45, 0.42, darkMaterial), [0, 0, 0]);
        add(roundedBox(1.08, 0.42, 0.08, glassMaterial), [0, 0.78, 0.28]);
        for (let row = 0; row < 3; row += 1) {
            for (let col = 0; col < 3; col += 1) {
                add(new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.12, 0.06, 24), faceMaterial), [(col - 1) * 0.38, 0.16 - row * 0.36, 0.3], [Math.PI / 2, 0, 0]);
            }
        }
        add(roundedBox(0.82, 0.1, 0.08, accentMaterial), [0, -0.92, 0.3]);
    }

    function setDevice(deviceKey) {
        const device = devices[deviceKey] || devices.hub;
        activeDevice = devices[deviceKey] ? deviceKey : 'hub';
        clearRoot();
        loadModel(activeDevice, device);
        root.rotation.set(-0.05, 0, 0);
        targetRotationX = -0.05;
        targetRotationY = 0;

        if (title) title.textContent = device.title;
        if (description) description.textContent = device.description;
        buttons.forEach((button) => {
            const isActive = button.dataset.viewerDevice === activeDevice;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
    }

    function resize() {
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(1, Math.floor(rect.width));
        const height = Math.max(1, Math.floor(rect.height));
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }

    function animate() {
        requestAnimationFrame(animate);
        if (!isVisible) return;
        root.rotation.y += (targetRotationY - root.rotation.y) * 0.08;
        root.rotation.x += (targetRotationX - root.rotation.x) * 0.08;
        if (!isDragging) targetRotationY += 0.0045;
        renderer.render(scene, camera);
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => setDevice(button.dataset.viewerDevice));
    });

    canvas.addEventListener('pointerdown', (event) => {
        isDragging = true;
        lastPointer = { x: event.clientX, y: event.clientY };
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!isDragging) return;
        const dx = event.clientX - lastPointer.x;
        const dy = event.clientY - lastPointer.y;
        targetRotationY += dx * 0.012;
        targetRotationX = Math.max(-0.72, Math.min(0.42, targetRotationX + dy * 0.008));
        lastPointer = { x: event.clientX, y: event.clientY };
    });

    canvas.addEventListener('pointerup', (event) => {
        isDragging = false;
        canvas.releasePointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointercancel', () => {
        isDragging = false;
    });

    if ('ResizeObserver' in window) {
        new ResizeObserver(resize).observe(canvas);
    } else {
        window.addEventListener('resize', resize);
    }

    if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            isVisible = entries.some((entry) => entry.isIntersecting);
        }, { threshold: 0.05 }).observe(canvas);
    }

    setDevice(activeDevice);
    resize();
    animate();
}());
