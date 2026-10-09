import Alpine from 'alpinejs';
import fileDropzone from './file-dropzone';

window.Alpine = Alpine;

Alpine.data('fileDropzone', fileDropzone);

Alpine.start();
