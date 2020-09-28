<!-- Modal -->
<div class="modal fade" id="ProgressFilesModal" tabindex="-1" role="dialog" aria-labelledby="ProgressFilesModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Novo anexo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                    <label for="description" class="col-sm-0 col-form-label text-md-left">Descrição</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <input v-model="currentFile.description" id="description" class="form-control{{ $errors->getBag('validation')->has('description') ? ' is-invalid' : '' }}"
                           name="description" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="drop1" class="col-sm-0 col-form-label text-md-left">Arquivo</label>
                    </div>
                </div>

                <input id="file_id" name="file_id" type="hidden" v-model="currentFile.id">

                <vue-dropzone v-on:vdropzone-success="fileUploaded" id="drop1" :options="dropOptions"></vue-dropzone>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" v-on:click="cancelModal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="addToFilesArray">Salvar</button>
            </div>
        </div>
    </div>
</div>
