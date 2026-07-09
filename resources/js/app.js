import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { createCalculatorComponent } from './calculator';

window.Chart = Chart;
window.createCalculatorComponent = createCalculatorComponent;

Alpine.data('heroImageUploader', (initialUrl = null) => ({
    initialUrl,
    preview: initialUrl,
    fileName: '',
    fileSize: '',

    onFileChange(event) {
        const file = event.target.files?.[0];

        if (! file) {
            this.clearBlobPreview();
            this.preview = this.initialUrl;
            this.fileName = '';
            this.fileSize = '';
            return;
        }

        if (! file.type.startsWith('image/')) {
            return;
        }

        this.clearBlobPreview();
        this.preview = URL.createObjectURL(file);
        this.fileName = file.name;
        this.fileSize = this.formatSize(file.size);
    },

    clearBlobPreview() {
        if (this.preview && String(this.preview).startsWith('blob:')) {
            URL.revokeObjectURL(this.preview);
        }
    },

    formatSize(bytes) {
        if (bytes < 1024) {
            return `${bytes} B`;
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    },
}));

window.Alpine = Alpine;
Alpine.start();
