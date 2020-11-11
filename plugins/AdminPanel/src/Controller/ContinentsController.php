<?php
namespace AdminPanel\Controller;

use AdminPanel\Controller\AppController;

/**
 * Continents Controller
 *
 * @property \AdminPanel\Model\Table\ContinentsTable $Continents
 *
 * @method \AdminPanel\Model\Entity\Continent[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ContinentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $continents = $this->paginate($this->Continents);

        $this->set(compact('continents'));
    }

    /**
     * View method
     *
     * @param string|null $id Continent id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $continent = $this->Continents->get($id, [
            'contain' => ['Countries'],
        ]);

        $this->set('continent', $continent);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $continent = $this->Continents->newEntity();
        if ($this->request->is('post')) {
            $continent = $this->Continents->patchEntity($continent, $this->request->getData());
            if ($this->Continents->save($continent)) {
                $this->Flash->success(__('The continent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The continent could not be saved. Please, try again.'));
        }
        $countries = $this->Continents->Countries->find('list');
        $this->set(compact('continent', 'countries'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Continent id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $continent = $this->Continents->get($id, [
            'contain' => ['Countries'],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $continent = $this->Continents->patchEntity($continent, $this->request->getData());
            if ($this->Continents->save($continent)) {
                $this->Flash->success(__('The continent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The continent could not be saved. Please, try again.'));
        }
        $countries = $this->Continents->Countries->find('list');
        $this->set(compact('continent', 'countries'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Continent id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $continent = $this->Continents->get($id);
        if ($this->Continents->delete($continent)) {
            $this->Flash->success(__('The continent has been deleted.'));
        } else {
            $this->Flash->error(__('The continent could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
