<!-- Modal de Delete -->
<div class="modal fade" id="delete-modal" data-modal-context="Excluir usuário" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabel">
                    Excluir Usuário
                </h4>

                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Deseja realmente excluir este usuário?
            </div>

            <div class="modal-footer">
                <form id="delete-form" method="post" action="delete.php" class="d-inline">
                    <input type="hidden" id="delete-id" name="id" value="">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button id="confirm" type="submit" class="btn btn-sm btn-light">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        Sim
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-dark" data-bs-dismiss="modal">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                    Não
                </button>
            </div>
        </div>
    </div>
</div>
