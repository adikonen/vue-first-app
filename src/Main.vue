<script setup>

/*
TODO 1 
BUAT LAH SEBUAH TABEL YANG BERISIKAN SEBUAH DATA DATA DARI BUAH

TODO 2 
BUATLAH SEBUAH VALIDASI REALTIME JIKA NAMA BUAH SUDAH ADA / INPUT KOSONG

TODO 3 
BUATLAH CRUD

TODO 4
BUAT LAH SEBUAH FILTER DATA BUAH


*/
import { reactive, ref, watch, computed, onMounted } from 'vue';

//helper func
const insert = (str, insertedStr, pos) => {
 return [str.slice(0, pos), insertedStr, str.slice(pos)].join('')
}

const toDigitString = (str) => {
    let dotted = (str.length % 3);
    let formatedPrice = insert(str, '.', dotted).split('.');

    let [left,right] = formatedPrice;

    for(let i = 0; i < right.length; i++){
        if(i % 3 === 0){
            left+='.';
        }
        left+=right[i];
    }   

    return left[0] === '.' ? left.slice(1) : left;
}
const toNormalString = (str) => str.split('.').join('');

const setSuccessAlert = (msg) => {
    show.fail.work = false;
    show.fail.message = '';
    show.fail.errors = [];
    show.success.work = true;
    show.success.message = msg;
}

const setFailAlert = (...msg) => {
    show.success.work = false;
    show.success.message = '';
    show.fail.work = true;
    show.fail.message = [...msg];
}

const getData = async(url) => {
    const raw = await fetch(url);
    const data = await raw.json();
    return data;
}

//deklarasi variabel
onMounted(() => {
console.log(getData('http://localhost/backend/'))
    
});

const fruits = ref([]);

const myFruits = computed( () => {
    const result = fruits.value.map(fruit => {
        let price = toDigitString(fruit.price);
        return {name: fruit.name, price: `${price}`}
    });

    return result;
})

const show = reactive({
    save:false, 
    success: {work: false, message: ''}, 
    fail: {work: false, message: []}
});

const inputName = ref('');
const inputPrice = ref('');


//validasi register
const validatedName = reactive({work: false, message: 'Input Nama harus diisi'});
const validatedPrice = reactive({work: false, message: 'Input Harga harus diisi'});

watch(inputName, (name) => {
    name.length === 0 ? validatedName.work = true : validatedName.work = false;
});

watch(inputPrice, (price) => {
    price.length === 0 ? validatedPrice.work = true : validatedPrice.work = false;
});

//register
const register = () => {

    if(inputName.value.length === 0 || inputPrice.value.length === 0 )
    {
        return setFailAlert('input nama atau harga tidak boleh kosong')
    }  
    //menghapus spasi di awal kata
    let data = inputName.value.trim();

    //validasi jika nama buah sudah ada
    for(let f of fruits.value){
        if(f.name === data)
        {
            return alert(`nama dari ${data} sudah ada`)
        }
    }

    //memasukan nama buah
    fruits.value.push({name: inputName.value, price: `${inputPrice.value}`});
    resetInput();
    return setSuccessAlert(`Berhasil menambahkan ${data} ke ruang penyimpanan!`);
}

const resetInput = () => {
    inputName.value = ' ';
    inputPrice.value = '0';
}

//simpan perubahan

const saveChanges = () => {
    const editedNames = document.getElementsByClassName("fruit-name");
    const editedPrices = document.getElementsByClassName("fruit-price"); 

    for(let i = 0; i < editedNames.length; i++) 
    {
        fruits.value[i].name = editedNames[i].innerText;
        fruits.value[i].price =  toNormalString(editedPrices[i].innerText);
    }
}

//validasi edit
//delete
const destroy = (index) => {
    let [trash] = fruits.value.splice(index, 1);
    setSuccessAlert(`Berhasil menghapus data ${trash.name}`);
}
</script>

<!-- HTML -->
<template>
    <div class="container">
        <div v-if="show.fail.work" class="alert alert-danger">
            <h3>Gagal!</h3>
            <ul v-for="error in show.fail.message">
                <li class="my-0">{{ error }}</li>
            </ul>
        </div>
        <div v-else-if="show.success.work" class="alert alert-success">
            <h3>Berhasil!</h3>
            <p>{{ show.success.message }}</p>
        </div>
        <table class="table text-center">
            <thead>
                <th>No</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Aksi</th>
            </thead>
            <tbody v-for="(fruit, index) of myFruits">
                <td>{{ index + 1 }}</td>
                <td class="fruit-name" contenteditable="true" @keyup="show.save = true">{{ fruit.name }}</td>
                <td>
                    Rp.
                    <span class="fruit-price" contenteditable="true" @keyup="show.save = true">
                        {{ fruit.price }}
                    </span> 
                </td>
                <td>
                    <button class="bg-danger fw-bold fc-white btn" @click="destroy(index)">Hapus</button>
                </td>
            </tbody>
        </table>

        <button class="btn btn-primary" v-if="show.save" @click="saveChanges">Simpan Perubahan</button>
    
        <form class="register row" method="POST">
            <div class="name px-3 py-1 col-md-6">
                <label for="name">Masukan Nama</label>
                <input 
                    type="text" 
                    v-model="inputName" 
                    name="name" id="name" 
                    class="form-control" 
                    :class="{'invalid' : validatedName.work}"
                >
                <span v-if="validatedName.work">{{ validatedName.message }}</span>
            </div>
            <div class="price py-1 px-3 col-md-6">
                <label for="price">Masukan Harga</label>
                <input 
                    type="number" 
                    v-model="inputPrice" 
                    class="form-control" 
                    name="price" 
                    id="price" 
                    :class="{'invalid' : validatedPrice.work}" 
                    placeholder="Contoh: 4000, 10000"
                >
                <span v-if="validatedPrice.work">{{ validatedPrice.message }}</span>
            </div>
            <div class="button-group row ps-4 pt-4">
                <button class="btn btn-success col-md-2" @click.prevent.default="register" :disabled="validatedName.work || validatedPrice.work">Tambah</button>
                <button type="reset" class="btn-warning btn col-md-2 mx-md-3 my-md-0 my-2">Reset</button>
           
            </div>
        </form>
    </div>
</template>

<style scoped>

.invalid {
    border: 1px solid red;
}
.fc-white {
    color:white;
}
</style>