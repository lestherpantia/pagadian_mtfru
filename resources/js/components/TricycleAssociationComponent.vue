<template>
    <div class="main-container p-4">
        <div style="position: absolute; top: 0; left: 0; z-index: 1000; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.2);" v-if="loader">
            <div style="position: fixed; top: 450px; left: 55%; transform:translate(-50%, -70%)">
                <img src="public/loader/loader.gif" alt="loader">
            </div>
        </div>
        <div style="position: absolute; top: 0; left: 0; z-index: 1000; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.2);" v-if="err || suc">
            <div style="position: fixed; top: 450px; left: 55%; transform:translate(-50%, -70%)">
                <div style="width: 500px; background: #F2F4F4;" class="p-3">
                    <div class="message-header p-2 border-bottom d-flex justify-content-between" style="background: #F2F4F4;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: bold" v-if="err">Error Message</h3>
                        <h3 style="margin: 0; font-size: 15px; font-weight: bold" v-if="suc">Success</h3>
                    </div>

                    <div class="message_box p-2 d-flex justify-content-between" style="position: relative" v-if="err">
                        <img style="width: 80px" class="mt-1 ml-1" src="public/image/icons/warning.png" alt="error">
                        <h1 style="font-size: 17px; position: absolute; top: 20px; left: 115px;">{{ err_msg }}</h1>
                    </div>

                    <div class="message_box p-2 d-flex justify-content-between" style="position: relative" v-if="suc">
                        <img style="width: 80px" class="mt-1 ml-1" src="public/image/icons/success.png" alt="error">
                        <h1 style="font-size: 17px; position: absolute; top: 20px; left: 115px;">{{ suc_msg }}</h1>
                    </div>

                    <div class="" style="position: absolute; bottom:20px; right:15px">
                        <button v-on:click="closeMessageBox" style="width: 100px;" class="btn btn-primary p-2">Ok</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-button d-flex justify-content-between pt-3 pb-3">
            <ul class="breadcrumb-1 pl-0 m-0">
                <li class="d-inline align-middle" style="font-size: 12px">System</li>
                <li class="d-inline align-middle mr-1 ml-1" style="font-size: 5px; color: #95a5a6;"><i class="fas fa-circle"></i></li>
                <li class="d-inline align-middle" style="color: #95a5a6; font-size: 12px">Association</li>
            </ul>
        </div>

        <div class="main-content">
            <h2 class="d-inline align-middle">Association</h2>
            <div class="row mt-3 mb-3">
                <div class="col-6">
                    <button v-on:click="openModalToCreate" class="d-inline align-middle p-2 pl-4 pr-4 rounded border-0" style="background: rgba(255, 120, 31, 1); color: #fff; font-size: 14px; box-shadow: 2px 2px 2px rgba(165, 165, 165, 1)">Add Association</button>
                </div>

                <div class="col-6 text-right">
                    <a class="p-2 pl-4 pr-4 rounded" style="background: #1abc9c; font-size: 14px; box-shadow: 2px 2px 2px rgba(165, 165, 165, 1); color: #fff; text-decoration: none;" v-on:click="exportExcel">Export to Excel</a>
                    <!--                    <a class="p-2 pl-4 pr-4 rounded" style="background: #1abc9c; font-size: 14px; box-shadow: 2px 2px 2px rgba(165, 165, 165, 1); color: #fff; text-decoration: none;" v-on:click="pdfModal">Print to PDF</a>-->
                </div>
            </div>

            <!--   Table     -->
            <div class="main-content-table">
                <div class="row">
                    <div class="col-md-12">
                        <v-client-table
                            :data="tableData"
                            :columns="columns"
                            :options="options">
                            <span slot="with_engine" slot-scope="props">
                                <p v-if="props.row.with_engine">Yes</p>
                                <p v-else>No</p>
                            </span>
                            <span slot="actions" slot-scope="{row}">
                                <button v-on:click="openModalMembers(row.id)" class="btn btn-primary mb-2"><i class="fa fa-plus mr-1" aria-hidden="true"></i> Add Member</button>
                                <button v-on:click="openModalToEdit(row.id)" class="btn btn-success mb-2"><i class="fas fa-edit mr-1"></i>Edit</button>
                                <button v-on:click="destroyRecord(row.id)" class="btn btn-danger mb-2"><i class="fas fa-trash mr-1"></i>Delete</button>
                            </span>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>

        <!--        modal window    -->

        <div class="modal fade" id="create-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 class="modal-title" id="exampleModalLabel" v-if="adding">Add Association</h5>
                        <h5 class="modal-title" id="exampleModalLabel" v-else>Edit Association</h5>

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 500px; overflow: auto;">
                        <div class="text-danger" v-if="errors.length > 0">
                            <ul>
                                <li v-for="error in errors">{{ error[0] }}</li>
                            </ul>
                        </div>

                        <div class="form-group" v-if="print">
                            <label for="paper-size">Paper Size</label>
                            <select id="paper-size" class="form-control">
                                <option>Letter</option>
                                <option>Legal</option>
                                <option>A4</option>
                            </select>

                            <label for="paper-orientation">Orientation</label>
                            <select id="paper-orientation" class="form-control">
                                <option>Portrait</option>
                                <option>Landscape</option>
                            </select>
                        </div>

                        <div class="form-group" v-else>
                            <label for="association">Association Name</label>
                            <input type="text" id="association" class="form-control" v-model="association.name">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button v-if="adding" @click="storeRecord" type="button" class="rounded pl-3 pr-3 pt-2 pb-2 border-0" style="width: 100px; font-size: 14px; background: #1abc9c; color: #fff;">Create</button>
                        <button v-else @click="updateRecord" type="button" class="rounded pl-3 pr-3 pt-2 pb-2 border-0" style="width: 100px; font-size: 14px; background: #1abc9c; color: #fff;">Update</button>
                        <button type="button" data-dismiss="modal" class="rounded pl-3 pr-3 pt-2 pb-2 border-0" style="width: 100px; font-size: 14px; background: #e74c3c; color: #fff;">Discard</button>
                    </div>
                </div>
            </div>
        </div>




        <div class="modal" id="member-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Member List</h5>
                    </div>
                    <div class="modal-body">

                        <div class="text-right mb-2">
                            <button v-on:click="showOperators" class="btn btn-success" style="font-size: 13px">Add Operator</button>
                        </div>

                        <v-client-table
                            :data="tableDataMembers"
                            :columns="columnsMembers"
                            :options="optionsMembers">
                            <span slot="actions" slot-scope="props">
                                <button v-on:click="removeMember(props)" class="btn btn-danger mb-2"><i class="fas fa-trash mr-1"></i>Delete</button>
                            </span>
                        </v-client-table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" style="font-size: 13px" v-on:click="saveMember">Save</button>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal" id="select-member-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Member List</h5>
                    </div>
                    <div class="modal-body">
                        <v-client-table
                            :data="tableDataMemberSelect"
                            :columns="columnsMemberSelect"
                            :options="optionsMemberSelect">
                            <span slot="actions" slot-scope="props">
                                <button v-on:click="selectOperatorData(props)" class="btn btn-success">Select</button>
                            </span>
                        </v-client-table>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>

export default {

    props: ['data'],

    data() {
        return {
            columns: ['name','created_at', 'updated_at','actions'],
            tableData: this.data,
            options: {
                headings: {
                    name: 'Association',
                    created_at: 'Created At',
                    updated_at: 'Updated At',
                    action: 'Action',
                },
                sortable: ['name'],
                filterable: ['name'],
                templates: {
                    hol_date: function(h, row) {
                        return row.hol_date !== null ? moment(row.hol_date).format('YYYY-MM-DD') : null;
                    },
                    created_at: function(h, row) {
                        return row.created_at !== null ? moment(row.created_at).format('YYYY-MM-DD hh:mm:ss') : null;
                    },
                    updated_at: function(h, row) {
                        return row.updated_at !== null ? moment(row.updated_at).format('YYYY-MM-DD hh:mm:ss') : null;
                    }
                },
                texts : {
                    filter: 'Search:',
                },

            },


            tableDataMembers : [],
            columnsMembers : ['taxpayer_id','operator','actions'],
            optionsMembers : {
                headings: {
                    taxpayer_id : 'Taxpayer ID',
                    operator: 'Name',
                    action: 'Action',
                },
                sortable: ['taxpayer_id','operator'],
                filterable: ['taxpayer_id','operator'],
                texts : {
                    filter: 'Search:',
                },
            },



            tableDataMemberSelect : [],
            columnsMemberSelect : ['taxpayer_id','operator','actions'],
            optionsMemberSelect : {
                headings: {
                    taxpayer_id : 'Taxpayer ID',
                    operator: 'Name',
                    action: 'Action',
                },
                sortable: ['taxpayer_id','operator'],
                filterable: ['taxpayer_id','operator'],
                texts : {
                    filter: 'Search:',
                },
            },



            //dropdowns
            errors: [],
            association: [],

            //values


            err_msg: '',
            err: false,
            suc_msg: '',
            suc: false,
            dataId : '',

            loader: false,
            adding: false,
            print: false,
            paperSize: 'Letter',
            paperOrientation: 'Portrait',
        }
    },
    methods: {

        errorHandler(errors){

            console.log(errors);
            let error_handler = [];
            $.each(errors, function(key, value) {
                error_handler.push(value);
            });
            return error_handler;
        },

        clearInput() {
            this.association = [];
            this.errors = [];
        },

        closeMessageBox() {
            this.err = false;
            this.suc = false;
            this.adding = false;
            this.print = false;
            this.err_msg = '';
            this.suc_msg = '';
            this.errors = [];
            location.reload();
        },

        openModalToCreate() {
            this.clearInput();
            $('#create-modal').modal('show');
            this.adding = true;
        },

        returnSuccess(response) {
            $('#create-modal').modal('hide');
            this.suc = true;
            this.suc_msg = response.data.message;
        },

        returnFailed(error) {
            if(error.response.data.err_msg) {
                this.err = true;
                this.err_msg = error.response.data.err_msg;
                $('#create-modal').modal('hide');
                return;
            }
            this.errors = [];
            this.errors = this.errorHandler(error.response.data.errors);
        },

        openModalToEdit(id) {
            axios.get('association/edit/' + id)
                .then(response => {
                    this.association = response.data.data;
                    $('#create-modal').modal('show');
                    this.adding = false;
                });
        },

        storeRecord() {
            this.loader = true;
            axios.post('association/store', {
                name: this.association.name,
            })
            .then(response => {
                this.returnSuccess(response);
            })
            .catch(error => {
                this.returnFailed(error);
            })
            .finally(
                () => this.loader = false
            )
        },

        updateRecord(){
            this.loader = true;
            axios.patch('association/update/', {
                id: this.association.id,
                name: this.association.name,
            })
            .then(response => {
                this.returnSuccess(response);
            })
            .catch(error => {
                this.returnFailed(error);
            })
            .finally(()=> this.loader = false);
        },

        destroyRecord(id) {
            let confirmBox = confirm("Do you really want to delete this record?");
            if(confirmBox === true)
            {
                this.loader = true;

                axios.get('association/destroy/' + id)
                    .then(response => {
                        this.returnSuccess(response);
                    })
                    .catch(error => {
                        this.returnFailed(error);
                    })
                    .finally(()=> this.loader = false);
            }
        },

        openModalMembers(id) {
            this.dataId = id;
            axios.get('association/showMembers/' + id).then(response => {
                this.tableDataMembers = response.data.data;
                $('#member-modal').modal('show');
            });
        },


        showOperators() {
            axios.get('association/showOperators').then(response => {
                this.tableDataMemberSelect = response.data.data;
                $('#select-member-modal').modal('show');
            });
        },

        selectOperatorData(props) {
            console.log(props.row.taxpayer_id);
            let isExist = false;
            for(const key in Object.keys(this.tableDataMembers)) {
                if(parseInt(this.tableDataMembers[key].taxpayer_id) === parseInt(props.row.taxpayer_id)) {
                    isExist = true;
                }
            }

            if(isExist) {
                alert('This member already exist');
                return true;
            }

            this.tableDataMembers.push(props.row);
            $('#select-member-modal').modal('hide');
        },

        removeMember(props) {
            let keyValue = 0;
            for(const key in Object.keys(this.tableDataMembers)) {
                if(this.tableDataMembers[key].id === props.row.id) {
                    keyValue = key;
                }
            }
            this.tableDataMembers.splice(keyValue, 1);
        },

        saveMember() {
            axios.post('association/saveMembers', {
                id : this.dataId,
                details : this.tableDataMembers
            })
            .then(response => {
                if(response.status === 200) {
                    alert('Data Successfully Saved');
                    location.reload();
                }
            })
        },

        exportExcel(){
            window.open('tricycle/export');
        },
    },

    mounted() {

    }
}
</script>
