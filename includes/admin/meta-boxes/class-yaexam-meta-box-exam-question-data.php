<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Meta_Box_Exam_Question_Data {
	
	public static function output( $post ) {
		global $post, $thepostid;
				
?>
			
    <div id="meta-box-exam-question-data">

        <div class="em-mt-3 em-mb-3">
            <button @click.prevent="() => (assignQuestionModal = true)" class="em-btn em-btn-primary em-btn-sm"><?php esc_html_e('Assign Questions', 'yaexam') ?></button>
            <button @click.prevent="() => (assignQuestionCategoryModal = true)" class="em-btn em-btn-primary em-btn-sm em-ml-1"><?php esc_html_e('Assign Question Category', 'yaexam') ?></button>
            
            <button @click.prevent="showSortQuestionModal" class="em-btn em-btn-info em-btn-sm em-ml-1"><?php esc_html_e('Sort', 'yaexam') ?></button>
            <button @click.prevent="unAssignQuestions" class="em-btn em-btn-danger em-btn-sm em-ml-1" :disabled="unAssignQuestionModalSelected.length == 0"><?php esc_html_e('Remove', 'yaexam') ?></button>
        </div>
        
        <div class="yaexam-table__table">

            <b-table ref="questionTable" 
                show-empty bordered sticky-header
                :items="loadData" 
                primary-key="id" 
                :busy.sync="isBusy" 
                :fields="tableFields" 
                :sort-by.sync="sortBy" 
                :sort-desc.sync="sortDesc" 
                responsive        
                :current-page="1" 
                :per-page="1000" selectable select-mode="multi" @row-selected="onUnAssignQuestionModalSelected">

                <template v-slot:cell(index)="row">
                    {{row.index + 1}}
                </template>
                
                <template v-slot:cell(selected)="{ rowSelected }">
                    <template v-if="rowSelected">
                        <button class="em-btn em-btn-success em-btn-sm"><b-icon-check-circle size="lg" aria-hidden="true"></b-icon-check-circle></button>
                    </template>
                    <template v-else>
                        <button class="em-btn em-btn-secondary em-btn-sm"><b-icon-check-circle size="lg" aria-hidden="true"></b-icon-check-circle></button>
                    </template>
                </template>

                <template v-slot:cell(actions)="row">
                    <div class="d-flex">

                        <button @click.prevent="edit(row.item.id)" class="em-btn em-btn-info em-btn-sm">
                            <b-icon-pencil-square aria-hidden="true"></b-icon-penci-squarel>
                        </button>

                    </div>
                </template>

                <template v-slot:table-busy>
                    <div class="text-center text-danger my-2">
                    <b-spinner class="align-middle" />
                    <strong><?php esc_html_e('Loading...', 'yaexam'); ?></strong>
                    </div>
                </template>
            </b-table>

        </div>

        <b-modal 
            id="modal-assign-question" 
            v-model="assignQuestionModal" 
            title="<?php esc_html_e('Questions', 'yaexam'); ?>" 
            @ok="submitAssignQuestions" 
            :centered="true" size="lg">

                <div class="em-mb-3">
                    <button class="em-btn em-btn-info em-btn-sm" @click.prevent="questionModalTableSelectAll"><?php esc_html_e('Select All', 'yaexam'); ?></button>
                    <button class="em-btn em-btn-info em-btn-sm" @click.prevent="questionModalTableClearAll"><?php esc_html_e('Clear All', 'yaexam'); ?></button>
                </div>

                <b-table ref="questionModalTable" responsive show-empty bordered striped hover
                        :items="loadQuestionModalData" primary-key="id" :busy.sync="isQuestionModalBusy"
                        :fields="tableQuestionModalFields" 
                        :sort-by.sync="questionModalSortBy" 
                        :sort-desc.sync="questionModalSortDesc" 
                        :current-page="questionModalCurrentPage" 
                        :per-page="questionModalPerpage" 
                        selectable select-mode="multi" @row-selected="onAssignQuestionModalSelected"
                        class="thead-light">

                    <template v-slot:cell(selected)="{ rowSelected }">
                        <template v-if="rowSelected">
                            <button class="em-btn em-btn-success em-btn-sm"><b-icon-check-circle size="lg" aria-hidden="true"></b-icon-check-circle></button>
                        </template>
                        <template v-else>
                            <button class="em-btn em-btn-secondary em-btn-sm"><b-icon-check-circle size="lg" aria-hidden="true"></b-icon-check-circle></button>
                        </template>
                    </template>

                    <template v-slot:cell(index)="row">

                        <b-form-checkbox 
                            v-model="row.item.checked"
                            value="1"
                            unchecked-value="0"
                            >
                            </b-form-checkbox>

                    </template>

                    <template v-slot:empty="scope">
                        <div class="em-text-center">
                            <a href="<?php echo admin_url('admin.php?page=em-questions'); ?>" class="em-btn em-btn-danger em-btn-sm"><?php esc_html_e('Add Question', 'yaexam'); ?></a>
                        </div>
                    </template>

                    <template v-slot:table-busy>
                        <div class="text-center text-danger my-2">
                        <b-spinner class="align-middle" />
                        <strong><?php esc_html_e('Loading...', 'yaexam'); ?></strong>
                        </div>
                    </template>
                </b-table>

                <b-pagination v-if="questionModalTotal > 0" v-model="questionModalCurrentPage" size="md" :total-rows="questionModalTotal" :per-page="questionModalPerpage" class="em-justify-content-end mb-0"/>

        </b-modal>

        <b-modal 
            id="modal-assign-question-category" 
            v-model="assignQuestionCategoryModal" 
            title="<?php esc_html_e('Question Categories', 'yaexam'); ?>" 
            @ok="submitAssignQuestionCategories" 
            :centered="true" size="lg">

            <b-table ref="questionCategoryModalTable" responsive show-empty bordered striped hover
                    :items="loadQuestionCategoryModalData" primary-key="id" :busy.sync="isQuestionCategoryModalBusy"
                    :fields="tableQuestionCategoryModalFields" 
                    class="thead-light">

                <template v-slot:cell(total)="row">

                    <input type="number" v-model="row.item.total" />

                </template>

                <template v-slot:empty="scope">
                    <div class="em-text-center">
                        <a href="<?php echo admin_url('admin.php?page=em-questions'); ?>" class="em-btn em-btn-danger em-btn-sm"><?php esc_html_e('Add Question', 'yaexam'); ?></a>
                    </div>
                </template>

                <template v-slot:table-busy>
                    <div class="text-center text-danger my-2">
                    <b-spinner class="align-middle" />
                    <strong><?php esc_html_e('Loading...', 'yaexam'); ?></strong>
                    </div>
                </template>
            </b-table>

        </b-modal>

        <b-modal 
            id="modal-edit" 
            v-model="editModal" 
            title="<?php esc_html_e('Edit', 'yaexam'); ?>" 
            @ok="submitEdit" 
            :centered="true" size="lg">

            <div class="form-group">
                <label><?php esc_html_e('Name', 'yaexam'); ?></label>
                <input type="text" class="form-control" v-model="editData.title"/>
            </div>

            <div v-if="editData.type == 'category'" class="form-group">
                <label><?php esc_html_e('Total', 'yaexam'); ?></label>
                <input type="number" min="0" class="form-control" v-model="editData.total"/>
            </div>

        </b-modal>

        <b-modal 
            id="modal-sort-question" 
            v-model="sortQuestionModal" 
            title="<?php esc_html_e('Sort Questions', 'yaexam'); ?>" 
            @ok="submitSortQuestion" 
            :centered="true" size="lg">
            <ul class="em-ml-0 em-list-group">
                <draggable v-model="sortQuestionModalData">
                    <transition-group>
                        <div class="em-list-group-item" v-for="element in sortQuestionModalData" :key="element.id">
                            {{element.title}}
                        </div>
                    </transition-group>
                </draggable>
            </ul>
        </b-modal>

    </div>
			
<?php 
	}
	
	public static function save( $post_id, $post ) {
		
		
	}
	
}