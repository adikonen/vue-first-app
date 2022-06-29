<script setup>
import { reactive, ref, watch, computed, inject } from 'vue';

//main var
const axios = inject('axios');  
const inputName = ref('');
const inputPrice = ref('');
const fruits = ref([]);

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

const setSuccessAlert = (alertObj,msg) => {
    alertObj.fail.work = false;
    alertObj.fail.message = '';
    alertObj.fail.errors = [];
    alertObj.success.work = true;
    alertObj.success.message = msg;
}

const setFailAlert = (alertObj, ...msg) => {
    alertObj.success.work = false;
    alertObj.success.message = '';
    alertObj.fail.work = true;
    alertObj.fail.message = [...msg];
}

const loadFruitData = (axios, url = 'http://localhost/backend/') => {
    axios.get(url)
        .then(response => fruits.value = response.data,
        reject => {
            console.log(reject.message)
            setFailAlert(show, 'oops sepertinya backend sedang ada masalah!')
        });
}

const pushFruit = ( 
    //parameter 
    axios,
    method = 'POST',
    body = {
        name: inputName.value,
        price: inputPrice.value
    }, 
    conf = {
        headers: {"Content-type": "application/json; charset=UTF-8"}
    },
    url = 'http://localhost/backend/', 
) => method === "POST" ?  axios.post(url, body, conf) :  axios.put(url,body,conf) ;
        

//deklarasi variabel
loadFruitData(axios);

const myFruits = computed( () => {
    let result = [];
    
    if(fruits.value != null)
    {
        result = fruits.value.map(fruit => {
            let price = toDigitString(fruit.price);
            return {name: fruit.name, price: `${price}`}
        });
    } 
    return result;
})

const show = reactive({
    save:false, 
    success: {work: false, message: '', title: 'Berhasil'}, 
    fail: {
        work: false,
        message: [], 
        title: 'Kesalahan',
    }
});



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
const register =  () => {

    if(inputName.value.length === 0 || inputPrice.value.length === 0 )
    {
        return setFailAlert(show,'input nama atau harga tidak boleh kosong')
    }  
    //menghapus spasi di awal kata
    let data = inputName.value.trim();

    //validasi jika nama buah sudah ada
    for(let f of fruits.value){
        if(f.name === data)
        {
            alert(`nama dari ${data} sudah ada`);
            return setFailAlert(show,`nama dari ${data} sudah ada`);
        }
    }

    //memasukan nama buah
    fruits.value.push({name: inputName.value, price: `${inputPrice.value}`});

    //menyetor ke database
  
    pushFruit(axios)
        .then( () => setSuccessAlert(show,`Berhasil menambahkan ${data} ke ruang penyimpanan!`))
        .catch( () =>  setFailAlert(show, 'Sepertinya ada kesalahan teknis pada backend, data tidak tersimpan pada penyimpanan!'))    

    resetInput();
}

const resetInput = () => {
    inputName.value = ' ';
    inputPrice.value = '0';
}

//simpan perubahan

const saveChanges =  () => {
    let price, name, no;
    const changes = [];
    show.fail.work = false;
    show.fail.message = [];
    const editedNames = document.getElementsByClassName("fruit-name");
    const editedPrices = document.getElementsByClassName("fruit-price"); 
    const error = {
        names : new Map(),
        price: new Map()
    }
    for(let i = 0; i < editedNames.length; i++) 
    {
        name = editedNames[i].innerText.trim();
        price =  toNormalString(editedPrices[i].innerText).trim();
        no = i+1;

        if(name.length === 0){
            error.names.set(i,`Field Nama pada nomor ${no} tidak boleh kosong!`);
        }
        //kalo if bernilai false, maka kode dibawah akan di esekusi
        else if(error.names.has(i)){
            error.names.delete(i);
        }

        if(price.length === 0){
            error.price.set(i, `Field Harga pada tabel nomor ${no} tidak boleh kosong!`);
        }
        else if(isNaN(price)){
            error.price.set(i, `Field Harga pada table nomor ${no} harus angka tidak boleh ada huruf!`)
        }
        else if(error.price.has(i)){
            error.price.delete(i);
        }

        //memilah buah apa saja yang telah diedit
        if(name !== fruits.value[i].name || price !== fruits.value[i].price){
            changes.push({
                newName: name,
                name: fruits.value[i].name,
                newPrice: price,
                price: fruits.value[i].price
            });
        }
     
        
    }
    //kalo tidak ada error, nilainya harus disimpan
    if(error.names.size === 0 && error.price.size === 0)
    {
        console.log('yeay');
        console.log(changes)
        for(let i = 0; i < editedNames.length; i++)
        {
            fruits.value[i].name = editedNames[i].innerText.trim();
            fruits.value[i].price = toNormalString(editedPrices[i].innerText).trim();
        }
        show.save = false;
        pushFruit(axios, 'PUT', changes)
            .then(() => setSuccessAlert(show, "seluruh data berhasil terupdate!"))
            .catch(() => setFailAlert(show, "oops sepertinya terdapat masalah pada backend! seluruh data tidak terupdate di penyimpanan")); 
    }
    else {
        error.names.forEach(name => show.fail.message.push(name));
        error.price.forEach(price => show.fail.message.push(price));

        show.fail.work = true;   
    }
    
}

//delete
const destroy =  (index) => {
    let [trash] = fruits.value.splice(index, 1);
    
    axios.delete("http://localhost/backend", {data: trash})
         .then(() => setSuccessAlert(show, `Berhasil menghapus ${trash.name}`))
         .catch(() => setFailAlert(show, "Oopps mungkin terjadi kesalahan pada backend! Gagal untuk menghapus"));
}
</script>

<!-- HTML -->
<template>
    <div class="container">
        <div v-if="show.fail.work" class="alert alert-danger">
            <h3>Kesalahan</h3>
            <ul v-for="error in show.fail.message"> 
                <li class="my-0">{{ error }}</li>
            </ul>
        </div>
        <div v-else-if="show.success.work" class="alert alert-success">
            <h3>Berhasil!</h3>
            <p>{{ show.success.message }}</p>
        </div>
        <h3 class="my-3">List Nama - Nama buah Terdaftar</h3>
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